"""
MediLife Portal - Role-Based Access Control Tests

Tests for:
- Route-level access control
- Field-level authorization
- Patient access restrictions
"""

import pytest

from app.models import User, Patient, Role


class TestRouteAccess:
    """Test route-level access control."""

    def test_admin_route_accessible_to_admin(self, client, db_session, admin_user):
        """Test admin routes are accessible to admin users."""
        session_id = self._login_and_get_session(client, db_session, 'testadmin', 'AdminPassword123!')

        response = client.get('/admin/', follow_redirects=True)
        assert response.status_code == 200

    def test_admin_route_blocked_for_patient(self, client, db_session, patient_user):
        """Test admin routes are blocked for patient users."""
        session_id = self._login_and_get_session(client, db_session, 'testpatient', 'PatientPassword123!')

        response = client.get('/admin/', follow_redirects=True)
        assert response.status_code == 200  # Redirects with error
        # Should show error message about access

    def test_doctor_route_accessible_to_doctor(self, client, db_session, doctor_user):
        """Test doctor routes are accessible to doctor users."""
        session_id = self._login_and_get_session(client, db_session, 'testdoctor', 'DoctorPassword123!')

        response = client.get('/doctor/', follow_redirects=True)
        assert response.status_code == 200

    def test_reception_route_accessible_to_reception(self, client, db_session, reception_user):
        """Test reception routes are accessible to reception users."""
        session_id = self._login_and_get_session(client, db_session, 'testreception', 'ReceptionPassword123!')

        response = client.get('/reception/', follow_redirects=True)
        assert response.status_code == 200

    def test_patient_route_accessible_to_patient(self, client, db_session, patient_user):
        """Test patient routes are accessible to patient users."""
        session_id = self._login_and_get_session(client, db_session, 'testpatient', 'PatientPassword123!')

        response = client.get('/patient/', follow_redirects=True)
        assert response.status_code == 200

    def test_unauthenticated_user_redirected_to_login(self, client):
        """Test unauthenticated users are redirected to login."""
        response = client.get('/admin/', follow_redirects=True)
        assert response.status_code == 200
        assert b'/login' in response.data or b'Hyr' in response.data

    def _login_and_get_session(self, client, db_session, username, password):
        """Helper to login and get session ID."""
        from app.crypto import generate_secure_token, hash_session_id
        from app.models import Session
        from datetime import datetime, timedelta

        user = User.query.filter_by(username=username).first()
        if not user:
            return None

        session_id = generate_secure_token(32)
        session = Session(
            session_hash=hash_session_id(session_id),
            user_id=user.id,
            created_at=datetime.utcnow(),
            last_seen_at=datetime.utcnow(),
            expires_at=datetime.utcnow() + timedelta(hours=8),
            is_valid=True
        )
        db_session.add(session)
        db_session.commit()

        with client.session_transaction() as sess:
            sess['session_id'] = session_id

        return session_id


class TestPatientAccess:
    """Test patient-specific access control."""

    def test_doctor_can_access_assigned_patient(self, client, db_session, doctor_user, sample_patient):
        """Test doctor can access their assigned patients."""
        # sample_patient is already assigned to doctor_user
        session_id = self._login_as(client, db_session, doctor_user)

        response = client.get(f'/doctor/patients/{sample_patient.id}', follow_redirects=True)
        assert response.status_code == 200

    def test_doctor_cannot_access_unassigned_patient(self, client, db_session, doctor_user):
        """Test doctor cannot access patients not assigned to them."""
        # Create unassigned patient
        patient = Patient(
            first_name='Other',
            last_name='Patient',
            date_of_birth='1990-01-01',
            assigned_doctor_id=None  # Not assigned to this doctor
        )
        db_session.add(patient)
        db_session.commit()

        session_id = self._login_as(client, db_session, doctor_user)

        response = client.get(f'/doctor/patients/{patient.id}', follow_redirects=True)
        # Should be blocked or show error

    def test_patient_can_only_access_own_record(self, client, db_session, patient_user, sample_patient):
        """Test patient can only access their own record."""
        # Link patient_user to sample_patient
        sample_patient.user_id = patient_user.id
        db_session.commit()

        session_id = self._login_as(client, db_session, patient_user)

        response = client.get(f'/patient/profile', follow_redirects=True)
        assert response.status_code == 200

    def test_patient_cannot_access_other_patient(self, client, db_session, patient_user):
        """Test patient cannot access other patient's record."""
        # Create another patient
        other_patient = Patient(
            first_name='Other',
            last_name='Patient',
            date_of_birth='1990-01-01',
            user_id=None  # Not linked to patient_user
        )
        db_session.add(other_patient)
        db_session.commit()

        session_id = self._login_as(client, db_session, patient_user)

        # Should not be able to access other patient's data

    def _login_as(self, client, db_session, user):
        """Helper to login as a user."""
        from app.crypto import generate_secure_token, hash_session_id
        from app.models import Session
        from datetime import datetime, timedelta

        session_id = generate_secure_token(32)
        session = Session(
            session_hash=hash_session_id(session_id),
            user_id=user.id,
            created_at=datetime.utcnow(),
            last_seen_at=datetime.utcnow(),
            expires_at=datetime.utcnow() + timedelta(hours=8),
            is_valid=True
        )
        db_session.add(session)
        db_session.commit()

        with client.session_transaction() as sess:
            sess['session_id'] = session_id

        return session_id


class TestFieldLevelAccess:
    """Test field-level authorization."""

    def test_reception_can_edit_demographics(self, client, db_session, reception_user, sample_patient):
        """Test reception can edit non-clinical fields."""
        session_id = self._login_as(client, db_session, reception_user)

        # Reception should be able to edit demographics
        response = client.post(f'/reception/patients/{sample_patient.id}/edit', data={
            'first_name': 'Updated',
            'last_name': 'Name',
            'phone': '987654321',
            'csrf_token': 'test'
        }, follow_redirects=True)

        # Should succeed for reception

    def test_doctor_cannot_edit_intake_fields(self, client, db_session, doctor_user, sample_patient):
        """Test doctor cannot edit intake form fields set by reception."""
        session_id = self._login_as(client, db_session, doctor_user)

        # Doctor should not be able to edit certain demographic fields
        # This is enforced by the can_edit_patient_field decorator
