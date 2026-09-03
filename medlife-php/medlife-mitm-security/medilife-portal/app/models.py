"""
MediLife Portal - Database Models

Secure data models with encryption support for sensitive fields.
"""

import uuid
from datetime import datetime, timedelta
from enum import Enum

from sqlalchemy.dialects.postgresql import JSONB
from werkzeug.security import generate_password_hash, check_password_hash

from . import db


class Role(Enum):
    """User roles for RBAC."""
    ADMIN = 'admin'
    DOCTOR = 'doctor'
    RECEPTION = 'reception'
    PATIENT = 'patient'


class User(db.Model):
    """
    User model for authentication and authorization.

    Supports Argon2id password hashing and role-based access control.
    """
    __tablename__ = 'users'

    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False, index=True)
    password_hash = db.Column(db.String(255), nullable=False)
    role = db.Column(db.Enum(Role), nullable=False, default=Role.PATIENT)
    active = db.Column(db.Boolean, default=True, nullable=False)
    last_login_at = db.Column(db.DateTime, nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow, nullable=False)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)

    # Relationships
    sessions = db.relationship('Session', backref='user', lazy='dynamic', cascade='all, delete-orphan')
    assigned_patients = db.relationship('Patient', backref='assigned_doctor', lazy='dynamic')
    audit_events = db.relationship('AuditEvent', backref='actor', lazy='dynamic')

    def set_password(self, password):
        """
        Hash password using Argon2id algorithm.

        Args:
            password: Plain text password to hash
        """
        from argon2 import PasswordHasher
        from argon2.exceptions import VerificationError

        ph = PasswordHasher(
            time_cost=3,
            memory_cost=65536,
            parallelism=4,
            hash_len=32
        )
        self.password_hash = ph.hash(password)

    def check_password(self, password):
        """
        Verify password against stored hash.

        Args:
            password: Plain text password to verify

        Returns:
            bool: True if password matches, False otherwise
        """
        from argon2 import PasswordHasher
        from argon2.exceptions import VerificationError, InvalidHash

        ph = PasswordHasher(
            time_cost=3,
            memory_cost=65536,
            parallelism=4,
            hash_len=32
        )

        try:
            ph.verify(self.password_hash, password)
            return True
        except (VerificationError, InvalidHash):
            return False

    def is_active_user(self):
        """Check if user account is active."""
        return self.active

    def has_role(self, role):
        """
        Check if user has specific role.

        Args:
            role: Role enum or string to check

        Returns:
            bool: True if user has the role
        """
        if isinstance(role, str):
            role = Role(role)
        return self.role == role

    def can_access_patient(self, patient_id):
        """
        Check if user can access a specific patient record.

        Args:
            patient_id: ID of the patient record

        Returns:
            bool: True if access is allowed
        """
        if self.role == Role.ADMIN:
            return True

        if self.role == Role.DOCTOR:
            patient = Patient.query.get(patient_id)
            return patient and patient.assigned_doctor_id == self.id

        if self.role == Role.PATIENT:
            patient = Patient.query.get(patient_id)
            return patient and patient.user_id == self.id

        return False

    def __repr__(self):
        return f'<User {self.username}>'


class Patient(db.Model):
    """
    Patient model with minimal non-sensitive demographics.

    Sensitive medical data is stored in PatientRecords with encryption.
    """
    __tablename__ = 'patients'

    id = db.Column(db.Integer, primary_key=True)
    first_name = db.Column(db.String(100), nullable=False)
    last_name = db.Column(db.String(100), nullable=False)
    date_of_birth = db.Column(db.Date, nullable=False)
    gender = db.Column(db.String(20), nullable=True)
    phone = db.Column(db.String(20), nullable=True)
    email = db.Column(db.String(120), nullable=True)
    address = db.Column(db.String(255), nullable=True)
    insurance_number = db.Column(db.String(50), nullable=True)

    # Foreign keys
    assigned_doctor_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=True, index=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=True, index=True)  # For patient portal access

    # Relationships
    records = db.relationship('PatientRecord', backref='patient', lazy='dynamic', cascade='all, delete-orphan')
    appointments = db.relationship('Appointment', backref='patient', lazy='dynamic', cascade='all, delete-orphan')

    # Audit relationship
    audit_events = db.relationship('AuditEvent', backref='patient_target', lazy='dynamic')

    @property
    def full_name(self):
        """Get patient's full name."""
        return f"{self.first_name} {self.last_name}"

    def __repr__(self):
        return f'<Patient {self.full_name}>'


class PatientRecord(db.Model):
    """
    Patient medical records with encrypted sensitive fields.

    Uses AES-256-GCM for encryption of diagnosis, treatment, and notes.
    """
    __tablename__ = 'patient_records'

    id = db.Column(db.Integer, primary_key=True)
    patient_id = db.Column(db.Integer, db.ForeignKey('patients.id'), nullable=False, index=True)
    created_by = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow, nullable=False)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)

    # Non-sensitive fields (stored in plaintext)
    record_type = db.Column(db.String(50), nullable=False)  # e.g., 'consultation', 'lab_result', 'prescription'
    visit_date = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)

    # Sensitive fields (encrypted with AES-256-GCM)
    _diagnosis = db.Column(db.LargeBinary, nullable=True)  # Encrypted
    _treatment = db.Column(db.LargeBinary, nullable=True)  # Encrypted
    _notes = db.Column(db.LargeBinary, nullable=True)  # Encrypted

    # Encryption metadata
    _diagnosis_nonce = db.Column(db.LargeBinary, nullable=True)
    _treatment_nonce = db.Column(db.LargeBinary, nullable=True)
    _notes_nonce = db.Column(db.LargeBinary, nullable=True)

    # Audit relationship
    audit_events = db.relationship('AuditEvent', backref='record_target', lazy='dynamic')

    @property
    def diagnosis(self):
        """Get decrypted diagnosis."""
        from .crypto import decrypt_field
        if self._diagnosis:
            return decrypt_field(self._diagnosis, self._diagnosis_nonce)
        return None

    @diagnosis.setter
    def diagnosis(self, value):
        """Set encrypted diagnosis."""
        from .crypto import encrypt_field
        if value:
            encrypted, nonce = encrypt_field(value)
            self._diagnosis = encrypted
            self._diagnosis_nonce = nonce
        else:
            self._diagnosis = None
            self._diagnosis_nonce = None

    @property
    def treatment(self):
        """Get decrypted treatment."""
        from .crypto import decrypt_field
        if self._treatment:
            return decrypt_field(self._treatment, self._treatment_nonce)
        return None

    @treatment.setter
    def treatment(self, value):
        """Set encrypted treatment."""
        from .crypto import encrypt_field
        if value:
            encrypted, nonce = encrypt_field(value)
            self._treatment = encrypted
            self._treatment_nonce = nonce
        else:
            self._treatment = None
            self._treatment_nonce = None

    @property
    def notes(self):
        """Get decrypted notes."""
        from .crypto import decrypt_field
        if self._notes:
            return decrypt_field(self._notes, self._notes_nonce)
        return None

    @notes.setter
    def notes(self, value):
        """Set encrypted notes."""
        from .crypto import encrypt_field
        if value:
            encrypted, nonce = encrypt_field(value)
            self._notes = encrypted
            self._notes_nonce = nonce
        else:
            self._notes = None
            self._notes_nonce = None

    def __repr__(self):
        return f'<PatientRecord {self.id} for Patient {self.patient_id}>'


class Appointment(db.Model):
    """
    Appointment model for scheduling patient visits.

    Accessible by reception (create/edit), doctor (view assigned),
    and patient (view own).
    """
    __tablename__ = 'appointments'

    id = db.Column(db.Integer, primary_key=True)
    patient_id = db.Column(db.Integer, db.ForeignKey('patients.id'), nullable=False, index=True)
    doctor_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    created_by = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    appointment_date = db.Column(db.DateTime, nullable=False)
    duration_minutes = db.Column(db.Integer, default=30)
    status = db.Column(db.String(20), default='scheduled', nullable=False)  # scheduled, completed, cancelled, no_show
    reason = db.Column(db.String(255), nullable=True)
    notes = db.Column(db.Text, nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow, nullable=False)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)

    # Relationships
    doctor = db.relationship('User', foreign_keys=[doctor_id], backref='doctor_appointments')
    creator = db.relationship('User', foreign_keys=[created_by], backref='created_appointments')

    # Audit relationship
    audit_events = db.relationship('AuditEvent', backref='appointment_target', lazy='dynamic')

    def __repr__(self):
        return f'<Appointment {self.id} for {self.patient_id} on {self.appointment_date}>'


class Session(db.Model):
    """
    Server-side session model for secure session management.

    Features:
    - Random opaque session IDs
    - Creation and last seen timestamps
    - Expiration tracking
    - Rotation support
    """
    __tablename__ = 'sessions'

    id = db.Column(db.Integer, primary_key=True)
    session_hash = db.Column(db.String(64), unique=True, nullable=False, index=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow, nullable=False)
    last_seen_at = db.Column(db.DateTime, default=datetime.utcnow, nullable=False)
    expires_at = db.Column(db.DateTime, nullable=False)
    is_valid = db.Column(db.Boolean, default=True, nullable=False)
    ip_address = db.Column(db.String(45), nullable=True)  # IPv6 compatible
    user_agent = db.Column(db.String(512), nullable=True)

    def is_expired(self):
        """Check if session has expired."""
        return datetime.utcnow() > self.expires_at

    def touch(self):
        """Update last seen timestamp."""
        self.last_seen_at = datetime.utcnow()

    def __repr__(self):
        return f'<Session {self.session_hash[:16]}... for User {self.user_id}>'


class AuditEvent(db.Model):
    """
    Audit event model for security logging.

    Records all security-relevant actions in the system.
    Logs to both PostgreSQL and rotating file.
    """
    __tablename__ = 'audit_events'

    id = db.Column(db.Integer, primary_key=True)
    actor_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=True, index=True)
    action = db.Column(db.String(100), nullable=False, index=True)
    target_type = db.Column(db.String(50), nullable=True)  # e.g., 'user', 'patient', 'record'
    target_id = db.Column(db.Integer, nullable=True)
    timestamp = db.Column(db.DateTime, default=datetime.utcnow, nullable=False, index=True)
    source_ip = db.Column(db.String(45), nullable=True)
    outcome = db.Column(db.String(20), nullable=False)  # 'success' or 'failure'
    details = db.Column(JSONB, nullable=True)

    # For patient-specific audits
    patient_id = db.Column(db.Integer, db.ForeignKey('patients.id'), nullable=True, index=True)

    def __repr__(self):
        return f'<AuditEvent {self.action} by {self.actor_id} at {self.timestamp}>'
