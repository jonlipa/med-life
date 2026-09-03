"""
MediLife Portal - Patient Routes

Patient self-service: view own profile, appointments, and records.
"""

from flask import Blueprint, render_template, request, redirect, url_for, flash, g
from flask_wtf import FlaskForm
from wtforms import StringField, PasswordField
from wtforms.validators import DataRequired, Length, Optional, Email

from .. import db
from ..models import Patient, Appointment, PatientRecord, Role
from ..decorators import patient_required, login_required
from ..audit import audit

patient_bp = Blueprint('patient', __name__)


class PatientProfileForm(FlaskForm):
    """Form for patients to update their own profile."""
    phone = StringField('Telefoni', validators=[Optional(), Length(max=20)])
    email = StringField('Email', validators=[Optional(), Email(), Length(max=120)])
    address = StringField('Adresa', validators=[Optional(), Length(max=255)])


class ChangePasswordForm(FlaskForm):
    """Form for changing password."""
    current_password = PasswordField('Fjalëkalimi Aktual', validators=[DataRequired()])
    new_password = PasswordField('Fjalëkalimi i Ri', validators=[
        DataRequired(),
        Length(min=8, message='Fjalëkalimi duhet të ketë të paktën 8 karaktere.')
    ])
    confirm_password = PasswordField('Konfirmo Fjalëkalimin', validators=[DataRequired()])


def get_patient_for_current_user():
    """
    Get the patient record associated with the current user.

    Returns:
        Patient record or None
    """
    if not g.current_user or g.current_user.role != Role.PATIENT:
        return None

    return Patient.query.filter_by(user_id=g.current_user.id).first()


@patient_bp.route('/')
@patient_required
def dashboard():
    """Patient dashboard."""
    patient = get_patient_for_current_user()

    if not patient:
        flash('Nuk ka profil pacienti të lidhur me llogarinë tuaj.', 'warning')
        return render_template('patient/dashboard.html', patient=None)

    # Get upcoming appointments
    from datetime import datetime
    upcoming_appointments = Appointment.query.filter(
        Appointment.patient_id == patient.id,
        Appointment.appointment_date >= datetime.utcnow(),
        Appointment.status == 'scheduled'
    ).order_by(Appointment.appointment_date).limit(3).all()

    # Get recent records (diagnosis/treatment only, not full details)
    recent_records = PatientRecord.query.filter_by(patient_id=patient.id).order_by(
        PatientRecord.created_at.desc()
    ).limit(5).all()

    return render_template('patient/dashboard.html',
                           patient=patient,
                           upcoming_appointments=upcoming_appointments,
                           recent_records=recent_records)


@patient_bp.route('/profile')
@patient_required
def profile():
    """View own profile."""
    patient = get_patient_for_current_user()

    if not patient:
        flash('Nuk ka profil pacienti të lidhur me llogarinë tuaj.', 'warning')
        return redirect(url_for('patient.dashboard'))

    # Audit access
    audit.log_record_access(patient.id, None)

    return render_template('patient/profile.html', patient=patient)


@patient_bp.route('/profile/edit', methods=['GET', 'POST'])
@patient_required
def edit_profile():
    """Edit own profile (limited fields)."""
    patient = get_patient_for_current_user()

    if not patient:
        flash('Nuk ka profil pacienti të lidhur me llogarinë tuaj.', 'warning')
        return redirect(url_for('patient.dashboard'))

    form = PatientProfileForm(obj=patient)

    if form.validate_on_submit():
        patient.phone = form.phone.data
        patient.email = form.email.data
        patient.address = form.address.data

        db.session.commit()

        audit.log_crud_action('EDIT_PROFILE', 'patient', patient.id, details={
            'fields': ['phone', 'email', 'address']
        })

        flash('Profili u përditësua me sukses.', 'success')
        return redirect(url_for('patient.profile'))

    return render_template('patient/profile_edit.html', form=form, patient=patient)


@patient_bp.route('/appointments')
@patient_required
def appointments():
    """View own appointments."""
    patient = get_patient_for_current_user()

    if not patient:
        return redirect(url_for('patient.dashboard'))

    appointments = Appointment.query.filter_by(patient_id=patient.id).order_by(
        Appointment.appointment_date.desc()
    ).all()

    return render_template('patient/appointments.html',
                           patient=patient,
                           appointments=appointments)


@patient_bp.route('/records')
@patient_required
def records():
    """View own medical records (limited view)."""
    patient = get_patient_for_current_user()

    if not patient:
        return redirect(url_for('patient.dashboard'))

    records = PatientRecord.query.filter_by(patient_id=patient.id).order_by(
        PatientRecord.created_at.desc()
    ).all()

    # Audit access
    for record in records:
        audit.log_record_access(patient.id, record.id)

    return render_template('patient/records.html',
                           patient=patient,
                           records=records)


@patient_bp.route('/change-password', methods=['GET', 'POST'])
@patient_required
def change_password():
    """Change own password."""
    form = ChangePasswordForm()

    if form.validate_on_submit():
        user = g.current_user

        # Verify current password
        if not user.check_password(form.current_password.data):
            flash('Fjalëkalimi aktual është gabim.', 'error')
            return render_template('patient/change_password.html', form=form)

        # Check password match
        if form.new_password.data != form.confirm_password.data:
            flash('Fjalëkalimet e reja nuk përputhen.', 'error')
            return render_template('patient/change_password.html', form=form)

        # Set new password
        user.set_password(form.new_password.data)
        db.session.commit()

        audit.log_crud_action('CHANGE_PASSWORD', 'user', user.id, details={
            'success': True
        })

        flash('Fjalëkalimi u ndryshua me sukses.', 'success')
        return redirect(url_for('patient.profile'))

    return render_template('patient/change_password.html', form=form)
