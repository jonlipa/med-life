"""
MediLife Portal - Doctor Routes

Doctor-specific functions: view assigned patients, edit medical records.
"""

from flask import Blueprint, render_template, request, redirect, url_for, flash, g
from flask_wtf import FlaskForm
from wtforms import StringField, TextAreaField, SelectField, DateField
from wtforms.validators import DataRequired, Optional

from datetime import datetime

from .. import db
from ..models import Patient, PatientRecord, Appointment, Role
from ..decorators import doctor_required, login_required, patient_access_required
from ..audit import audit

doctor_bp = Blueprint('doctor', __name__)


class PatientRecordForm(FlaskForm):
    """Form for creating/editing patient medical records."""
    record_type = SelectField('Tipi i Regjistrimit', choices=[
        ('consultation', 'Konsultë'),
        ('lab_result', 'Rezultate Laboratori'),
        ('prescription', 'Recetë'),
        ('diagnosis', 'Diagnozë'),
        ('other', 'Tjetër')
    ], validators=[DataRequired()])
    diagnosis = TextAreaField('Diagnoza')
    treatment = TextAreaField('Trajtimi')
    notes = TextAreaField('Shënime')


class AppointmentForm(FlaskForm):
    """Form for creating appointments."""
    appointment_date = DateField('Data dhe Ora', format='%Y-%m-%dT%H:%M', validators=[DataRequired()])
    reason = StringField('Arsyeja', validators=[Optional(), Length(max=255)])
    duration_minutes = SelectField('Kohëzgjatja (minuta)', choices=[
        (15, '15 minuta'),
        (30, '30 minuta'),
        (45, '45 minuta'),
        (60, '1 orë')
    ], default=30)


@doctor_bp.route('/')
@doctor_required
def dashboard():
    """Doctor dashboard."""
    # Get assigned patients count
    assigned_patients = Patient.query.filter_by(assigned_doctor_id=g.current_user.id).count()

    # Get upcoming appointments
    upcoming_appointments = Appointment.query.filter(
        Appointment.doctor_id == g.current_user.id,
        Appointment.appointment_date >= datetime.utcnow(),
        Appointment.status == 'scheduled'
    ).order_by(Appointment.appointment_date).limit(5).all()

    return render_template('doctor/dashboard.html',
                           assigned_patients=assigned_patients,
                           upcoming_appointments=upcoming_appointments)


@doctor_bp.route('/patients')
@doctor_required
def patients():
    """List doctor's assigned patients."""
    patients = Patient.query.filter_by(assigned_doctor_id=g.current_user.id).order_by(Patient.last_name).all()
    return render_template('doctor/patients.html', patients=patients)


@doctor_bp.route('/patients/<int:patient_id>')
@doctor_required
@patient_access_required('patient_id')
def view_patient(patient_id):
    """View a specific patient's details and records."""
    patient = Patient.query.get_or_404(patient_id)
    records = PatientRecord.query.filter_by(patient_id=patient_id).order_by(
        PatientRecord.created_at.desc()
    ).all()
    appointments = Appointment.query.filter_by(patient_id=patient_id).order_by(
        Appointment.appointment_date.desc()
    ).limit(10).all()

    # Audit: record access
    audit.log_record_access(patient_id, None)

    return render_template('doctor/patient_view.html',
                           patient=patient,
                           records=records,
                           appointments=appointments)


@doctor_bp.route('/patients/<int:patient_id>/records/new', methods=['GET', 'POST'])
@doctor_required
@patient_access_required('patient_id')
def create_record(patient_id):
    """Create a new medical record for a patient."""
    patient = Patient.query.get_or_404(patient_id)
    form = PatientRecordForm()

    if form.validate_on_submit():
        record = PatientRecord(
            patient_id=patient_id,
            created_by=g.current_user.id,
            record_type=form.record_type.data,
            visit_date=datetime.utcnow(),
            diagnosis=form.diagnosis.data or None,
            treatment=form.treatment.data or None,
            notes=form.notes.data or None
        )

        db.session.add(record)
        db.session.commit()

        audit.log_record_edit(patient_id, record.id, 'create')

        flash('Regjistrimi u krijua me sukses.', 'success')
        return redirect(url_for('doctor.view_patient', patient_id=patient_id))

    return render_template('doctor/record_form.html', form=form, patient=patient, title='Krijo Regjistrim')


@doctor_bp.route('/records/<int:record_id>/edit', methods=['GET', 'POST'])
@doctor_required
def edit_record(record_id):
    """Edit an existing medical record."""
    record = PatientRecord.query.get_or_404(record_id)
    patient = Patient.query.get_or_404(record.patient_id)

    # Check access
    if not g.current_user.can_access_patient(patient.id):
        flash('Nuk keni akses për të edituar këtë regjistrim.', 'error')
        return redirect(url_for('doctor.dashboard'))

    form = PatientRecordForm(obj=record)

    if form.validate_on_submit():
        record.record_type = form.record_type.data
        record.diagnosis = form.diagnosis.data or None
        record.treatment = form.treatment.data or None
        record.notes = form.notes.data or None
        record.updated_at = datetime.utcnow()

        db.session.commit()

        audit.log_record_edit(patient.id, record.id, 'edit')

        flash('Regjistrimi u përditësua me sukses.', 'success')
        return redirect(url_for('doctor.view_patient', patient_id=patient.id))

    # Pre-populate encrypted fields
    form.diagnosis.data = record.diagnosis
    form.treatment.data = record.treatment
    form.notes.data = record.notes

    return render_template('doctor/record_form.html', form=form, patient=patient, record=record, title='Edito Regjistrim')


@doctor_bp.route('/appointments')
@doctor_required
def appointments():
    """View doctor's appointments."""
    appointments = Appointment.query.filter_by(doctor_id=g.current_user.id).order_by(
        Appointment.appointment_date.desc()
    ).limit(50).all()
    return render_template('doctor/appointments.html', appointments=appointments)


@doctor_bp.route('/appointments/<int:appointment_id>/complete', methods=['POST'])
@doctor_required
def complete_appointment(appointment_id):
    """Mark an appointment as completed."""
    appointment = Appointment.query.get_or_404(appointment_id)

    if appointment.doctor_id != g.current_user.id and g.current_user.role != Role.ADMIN:
        flash('Nuk keni akses për këtë veprim.', 'error')
        return redirect(url_for('doctor.appointments'))

    appointment.status = 'completed'
    db.session.commit()

    audit.log_crud_action('COMPLETE_APPOINTMENT', 'appointment', appointment_id)

    flash('Programimi u shënua si i përfunduar.', 'success')
    return redirect(url_for('doctor.appointments'))
