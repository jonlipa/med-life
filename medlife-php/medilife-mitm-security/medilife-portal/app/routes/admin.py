"""
MediLife Portal - Admin Routes

Administrative functions: user management, audit logs, doctor assignments.
"""

from flask import Blueprint, render_template, request, redirect, url_for, flash, g
from flask_wtf import FlaskForm
from wtforms import StringField, SelectField, BooleanField, PasswordField
from wtforms.validators import DataRequired, Length, Optional, Email

from .. import db
from ..models import User, Patient, Role, AuditEvent
from ..decorators import admin_required, login_required
from ..audit import audit

admin_bp = Blueprint('admin', __name__)


class UserForm(FlaskForm):
    """Form for creating/editing users."""
    username = StringField('Emri i përdoruesit', validators=[
        DataRequired(),
        Length(min=3, max=80)
    ])
    password = PasswordField('Fjalëkalimi', validators=[
        Length(min=8, message='Fjalëkalimi duhet të ketë të paktën 8 karaktere.')
    ])
    role = SelectField('Roli', choices=[
        ('admin', 'Administrator'),
        ('doctor', 'Mjek'),
        ('reception', 'Recepcion'),
        ('patient', 'Pacient')
    ], validators=[DataRequired()])
    active = BooleanField('Aktiv')


class PatientAssignmentForm(FlaskForm):
    """Form for assigning doctors to patients."""
    doctor_id = SelectField('Mjeku', coerce=int, validators=[DataRequired()])


@admin_bp.route('/')
@admin_required
def dashboard():
    """Admin dashboard."""
    # Get stats
    total_users = User.query.count()
    total_patients = Patient.query.count()
    total_doctors = User.query.filter_by(role=Role.DOCTOR).count()
    recent_events = AuditEvent.query.order_by(AuditEvent.timestamp.desc()).limit(10).all()

    return render_template('admin/dashboard.html',
                           total_users=total_users,
                           total_patients=total_patients,
                           total_doctors=total_doctors,
                           recent_events=recent_events)


@admin_bp.route('/users')
@admin_required
def users():
    """List all users."""
    users = User.query.order_by(User.username).all()
    return render_template('admin/users.html', users=users)


@admin_bp.route('/users/new', methods=['GET', 'POST'])
@admin_required
def create_user():
    """Create a new user."""
    form = UserForm()

    if form.validate_on_submit():
        # Check if username exists
        existing = User.query.filter_by(username=form.username.data).first()
        if existing:
            flash('Ky emër përdoruesi është tashmë i zënë.', 'error')
            return render_template('admin/user_form.html', form=form)

        # Create user
        user = User(
            username=form.username.data,
            role=Role(form.role.data),
            active=form.active.data
        )

        if form.password.data:
            user.set_password(form.password.data)

        db.session.add(user)
        db.session.commit()

        audit.log_admin_action('CREATE_USER', 'user', user.id, {
            'username': user.username,
            'role': user.role.value
        })

        flash('Përdoruesi u krijua me sukses.', 'success')
        return redirect(url_for('admin.users'))

    return render_template('admin/user_form.html', form=form, title='Krijo Përdorues')


@admin_bp.route('/users/<int:user_id>/edit', methods=['GET', 'POST'])
@admin_required
def edit_user(user_id):
    """Edit an existing user."""
    user = User.query.get_or_404(user_id)
    form = UserForm(obj=user)

    # Remove password validator for edit
    form.password.validators = [Optional()]

    if form.validate_on_submit():
        user.username = form.username.data
        user.role = Role(form.role.data)
        user.active = form.active.data

        if form.password.data:
            user.set_password(form.password.data)

        db.session.commit()

        audit.log_admin_action('EDIT_USER', 'user', user.id, {
            'username': user.username,
            'role': user.role.value
        })

        flash('Përdoruesi u përditësua me sukses.', 'success')
        return redirect(url_for('admin.users'))

    # Pre-populate form
    form.role.data = user.role.value
    form.active.data = user.active

    return render_template('admin/user_form.html', form=form, title='Edito Përdorues', user=user)


@admin_bp.route('/users/<int:user_id>/delete', methods=['POST'])
@admin_required
def delete_user(user_id):
    """Delete a user."""
    user = User.query.get_or_404(user_id)

    # Prevent self-deletion
    if user.id == g.current_user.id:
        flash('Nuk mund të fshini llogarinë tuaj.', 'error')
        return redirect(url_for('admin.users'))

    username = user.username
    db.session.delete(user)
    db.session.commit()

    audit.log_admin_action('DELETE_USER', 'user', user_id, {
        'username': username
    })

    flash('Përdoruesi u fshi me sukses.', 'success')
    return redirect(url_for('admin.users'))


@admin_bp.route('/audit')
@admin_required
def audit_log():
    """View audit log."""
    page = request.args.get('page', 1, type=int)
    action_filter = request.args.get('action', '')
    outcome_filter = request.args.get('outcome', '')

    query = AuditEvent.query.order_by(AuditEvent.timestamp.desc())

    if action_filter:
        query = query.filter(AuditEvent.action.ilike(f'%{action_filter}%'))
    if outcome_filter:
        query = query.filter(AuditEvent.outcome == outcome_filter)

    pagination = query.paginate(page=page, per_page=50, error_out=False)
    events = pagination.items

    return render_template('admin/audit.html',
                           events=events,
                           pagination=pagination,
                           action_filter=action_filter,
                           outcome_filter=outcome_filter)


@admin_bp.route('/patients/<int:patient_id>/assign', methods=['GET', 'POST'])
@admin_required
def assign_doctor(patient_id):
    """Assign a doctor to a patient."""
    patient = Patient.query.get_or_404(patient_id)
    form = PatientAssignmentForm()

    # Populate doctor choices
    doctors = User.query.filter_by(role=Role.DOCTOR, active=True).all()
    form.doctor_id.choices = [(d.id, d.username) for d in doctors]

    if form.validate_on_submit():
        patient.assigned_doctor_id = form.doctor_id.data
        db.session.commit()

        audit.log_admin_action('ASSIGN_DOCTOR', 'patient', patient_id, {
            'doctor_id': form.doctor_id.data
        })

        flash('Mjeku u caktua me sukses.', 'success')
        return redirect(url_for('admin.patients'))

    # Pre-populate
    if patient.assigned_doctor_id:
        form.doctor_id.data = patient.assigned_doctor_id

    return render_template('admin/assign_doctor.html', form=form, patient=patient)


@admin_bp.route('/patients')
@admin_required
def patients():
    """List all patients (admin view)."""
    patients = Patient.query.order_by(Patient.last_name).all()
    return render_template('admin/patients.html', patients=patients)
