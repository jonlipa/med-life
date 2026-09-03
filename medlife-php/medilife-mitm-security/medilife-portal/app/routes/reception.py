"""
MediLife Portal - Reception Routes

Reception-specific functions: patient intake, appointments, demographics.
"""

from flask import Blueprint, render_template, request, redirect, url_for, flash, g
from flask_wtf import FlaskForm
from wtforms import StringField, SelectField, DateField, TextAreaField
from wtforms.validators import DataRequired, Optional, Email, Length

from datetime import datetime, date

from .. import db
from ..models import Patient, Appointment, User, Role
from ..decorators import reception_required, login_required
from ..audit import audit

reception_bp = Blueprint('reception', __name__)


class PatientIntakeForm(FlaskForm):
    """Form for patient intake (non-clinical demographics)."""
    first_name = StringField('Emri', validators=[DataRequired(), Length(max=100)])
    last_name = StringField('Mbiemri', validators=[DataRequired(), Length(max=100)])
    date_of_birth = DateField('Data e Lindjes', format='%Y-%m-%d', validators=[DataRequired()])
    gender = SelectField('Gjinia', choices=[
        ('', 'Zgjidh...'),
        ('male', 'Mashkull'),
        ('female', 'Femër'),
        ('other', 'Tjetër')
    ], validators=[Optional()])
    phone = StringField('Telefoni', validators=[Optional(), Length(max=20)])
    email = StringField('Email', validators=[Optional(), Email(), Length(max=120)])
    address = TextAreaField('Adresa', validators=[Optional(), Length(max=255)])
    insurance_number = StringField('Numri i Sigurimit', validators=[Optional(), Length(max=50)])


class AppointmentForm(FlaskForm):
    """Form for creating appointments."""
    patient_id = SelectField('Pacienti', coerce=int, validators=[DataRequired()])
    doctor_id = SelectField('Mjeku', coerce=int, validators=[DataRequired()])
    appointment_date = DateField('Data dhe Ora', format='%Y-%m-%dT%H:%M', validators=[DataRequired()])
    reason = StringField('Arsyeja', validators=[Optional(), Length(max=255)])
    duration_minutes = SelectField('Kohëzgjatja (minuta)', choices=[
        (15, '15 minuta'),
        (30, '30 minuta'),
        (45, '45 minuta'),
        (60, '1 orë')
    ], default=30)


@reception_bp.route('/')
@reception_required
def dashboard():
    """Reception dashboard."""
    today = date.today()
    today_appointments = Appointment.query.filter(
        db.func.date(Appointment.appointment_date) == today
    ).count()

    total_patients = Patient.query.count()
    pending_intake = Patient.query.filter(
        Patient.insurance_number == None
    ).count()

    return render_template('reception/dashboard.html',
                           today_appointments=today_appointments,
                           total_patients=total_patients,
                           pending_intake=pending_intake)


@reception_bp.route('/patients')
@reception_required
def patients():
    """List all patients (reception view)."""
    search = request.args.get('search', '')
    query = Patient.query

    if search:
        query = query.filter(
            db.or_(
                Patient.first_name.ilike(f'%{search}%'),
                Patient.last_name.ilike(f'%{search}%'),
                Patient.insurance_number.ilike(f'%{search}%')
            )
        )

    patients = query.order_by(Patient.last_name).limit(100).all()
    return render_template('reception/patients.html', patients=patients, search=search)


@reception_bp.route('/patients/new', methods=['GET', 'POST'])
@reception_required
def create_patient():
    """Create a new patient (intake)."""
    form = PatientIntakeForm()

    if form.validate_on_submit():
        patient = Patient(
            first_name=form.first_name.data,
            last_name=form.last_name.data,
            date_of_birth=form.date_of_birth.data,
            gender=form.gender.data,
            phone=form.phone.data,
            email=form.email.data,
            address=form.address.data,
            insurance_number=form.insurance_number.data
        )

        db.session.add(patient)
        db.session.commit()

        audit.log_crud_action('CREATE_PATIENT', 'patient', patient.id, details={
            'name': patient.full_name
        })

        flash('Pacienti u regjistrua me sukses.', 'success')
        return redirect(url_for('reception.view_patient', patient_id=patient.id))

    return render_template('reception/patient_form.html', form=form, title='Regjistro Pacient të Ri')


@reception_bp.route('/patients/<int:patient_id>')
@reception_required
def view_patient(patient_id):
    """View patient demographics."""
    patient = Patient.query.get_or_404(patient_id)
    appointments = Appointment.query.filter_by(patient_id=patient_id).order_by(
        Appointment.appointment_date.desc()
    ).limit(10).all()

    return render_template('reception/patient_view.html',
                           patient=patient,
                           appointments=appointments)


@reception_bp.route('/patients/<int:patient_id>/edit', methods=['GET', 'POST'])
@reception_required
def edit_patient(patient_id):
    """Edit patient demographics (non-clinical fields only)."""
    patient = Patient.query.get_or_404(patient_id)
    form = PatientIntakeForm(obj=patient)

    if form.validate_on_submit():
        patient.first_name = form.first_name.data
        patient.last_name = form.last_name.data
        patient.date_of_birth = form.date_of_birth.data
        patient.gender = form.gender.data
        patient.phone = form.phone.data
        patient.email = form.email.data
        patient.address = form.address.data
        patient.insurance_number = form.insurance_number.data

        db.session.commit()

        audit.log_crud_action('EDIT_PATIENT', 'patient', patient_id, details={
            'fields': 'demographics'
        })

        flash('Të dhënat e pacientit u përditësuan me sukses.', 'success')
        return redirect(url_for('reception.view_patient', patient_id=patient.id))

    return render_template('reception/patient_form.html', form=form, patient=patient, title='Edito Pacientin')


@reception_bp.route('/appointments')
@reception_required
def appointments():
    """View all appointments."""
    today = date.today()
    upcoming = Appointment.query.filter(
        Appointment.appointment_date >= datetime.combine(today, datetime.min.time())
    ).order_by(Appointment.appointment_date).limit(50).all()

    return render_template('reception/appointments.html', appointments=upcoming)


@reception_bp.route('/appointments/new', methods=['GET', 'POST'])
@reception_required
def create_appointment():
    """Create a new appointment."""
    form = AppointmentForm()

    # Populate patient choices
    patients = Patient.query.order_by(Patient.last_name).limit(100).all()
    form.patient_id.choices = [(p.id, p.full_name) for p in patients]

    # Populate doctor choices
    doctors = User.query.filter_by(role=Role.DOCTOR, active=True).all()
    form.doctor_id.choices = [(d.id, d.username) for d in doctors]

    if form.validate_on_submit():
        appointment = Appointment(
            patient_id=form.patient_id.data,
            doctor_id=form.doctor_id.data,
            created_by=g.current_user.id,
            appointment_date=datetime.strptime(form.appointment_date.data, '%Y-%m-%dT%H:%M'),
            duration_minutes=int(form.duration_minutes.data),
            reason=form.reason.data,
            status='scheduled'
        )

        db.session.add(appointment)
        db.session.commit()

        audit.log_crud_action('CREATE_APPOINTMENT', 'appointment', appointment.id)

        flash('Programimi u krijua me sukses.', 'success')
        return redirect(url_for('reception.appointments'))

    return render_template('reception/appointment_form.html', form=form, title='Krijo Programim')


@reception_bp.route('/appointments/<int:appointment_id>/cancel', methods=['POST'])
@reception_required
def cancel_appointment(appointment_id):
    """Cancel an appointment."""
    appointment = Appointment.query.get_or_404(appointment_id)
    appointment.status = 'cancelled'
    db.session.commit()

    audit.log_crud_action('CANCEL_APPOINTMENT', 'appointment', appointment_id)

    flash('Programimi u anullua.', 'info')
    return redirect(url_for('reception.appointments'))
