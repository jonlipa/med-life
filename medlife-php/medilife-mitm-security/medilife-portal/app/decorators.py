"""
MediLife Portal - RBAC Decorators

Role-based access control decorators for route protection.
"""

from functools import wraps
from flask import flash, redirect, url_for, session, abort
from flask_login import current_user

from .models import Role


def role_required(*roles):
    """
    Decorator to require specific roles for accessing a route.

    Args:
        *roles: One or more Role enums or role names

    Returns:
        Decorated function that checks user role

    Example:
        @app.route('/admin')
        @role_required(Role.ADMIN)
        def admin_panel():
            ...
    """
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            # Check if user is authenticated
            if not current_user.is_authenticated:
                return redirect(url_for('auth.login', next=request.path))

            # Convert string roles to Role enums
            required_roles = []
            for role in roles:
                if isinstance(role, str):
                    required_roles.append(Role(role))
                else:
                    required_roles.append(role)

            # Check if user has any of the required roles
            if current_user.role not in required_roles:
                flash('Nuk keni akses për të parë këtë faqe.', 'error')
                abort(403)

            return f(*args, **kwargs)
        return decorated_function
    return decorator


def admin_required(f):
    """
    Decorator to require admin role.

    Shortcut for @role_required(Role.ADMIN)
    """
    return role_required(Role.ADMIN)(f)


def doctor_required(f):
    """
    Decorator to require doctor role.

    Allows both admin and doctor roles.
    """
    return role_required(Role.ADMIN, Role.DOCTOR)(f)


def reception_required(f):
    """
    Decorator to require reception role.

    Allows admin and reception roles.
    """
    return role_required(Role.ADMIN, Role.RECEPTION)(f)


def patient_required(f):
    """
    Decorator to require patient role.

    Allows admin and patient roles.
    """
    return role_required(Role.ADMIN, Role.PATIENT)(f)


def can_edit_patient_field(field_name: str):
    """
    Decorator to check if user can edit a specific patient field.

    Field-level authorization for granular access control.

    Args:
        field_name: Name of the field being edited

    Returns:
        Decorated function that checks field-level permissions
    """
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            if not current_user.is_authenticated:
                return redirect(url_for('auth.login'))

            # Admin can edit all fields
            if current_user.role == Role.ADMIN:
                return f(*args, **kwargs)

            # Reception can only edit non-clinical fields
            if current_user.role == Role.RECEPTION:
                allowed_fields = ['first_name', 'last_name', 'phone', 'email', 'address', 'insurance_number']
                if field_name not in allowed_fields:
                    flash('Nuk keni akses për të edituar këtë fushë.', 'error')
                    abort(403)
                return f(*args, **kwargs)

            # Doctors cannot edit intake forms (non-clinical fields set by reception)
            if current_user.role == Role.DOCTOR:
                intake_fields = ['first_name', 'last_name', 'phone', 'email', 'address', 'insurance_number']
                if field_name in intake_fields:
                    flash('Mjekët nuk mund të editojnë të dhënat e recepcionit.', 'error')
                    abort(403)
                return f(*args, **kwargs)

            # Patients cannot edit their own profile via this endpoint
            if current_user.role == Role.PATIENT:
                flash('Pacientët nuk mund të editojnë këtë fushë.', 'error')
                abort(403)

            abort(403)
        return decorated_function
    return decorator


def patient_access_required(patient_id_param: str = 'patient_id'):
    """
    Decorator to check if user can access a specific patient record.

    Implements patient-level authorization:
    - Admin: can access all patients
    - Doctor: can access assigned patients
    - Reception: can access all patients (limited fields)
    - Patient: can only access own record

    Args:
        patient_id_param: Name of the URL parameter containing patient ID

    Returns:
        Decorated function that checks patient access
    """
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            if not current_user.is_authenticated:
                return redirect(url_for('auth.login'))

            patient_id = kwargs.get(patient_id_param)

            if not current_user.can_access_patient(patient_id):
                flash('Nuk keni akses për të parë këtë rekord pacienti.', 'error')
                abort(403)

            return f(*args, **kwargs)
        return decorated_function
    return decorator


# Import request here to avoid circular imports
from flask import request
