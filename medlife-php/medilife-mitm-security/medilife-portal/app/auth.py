"""
MediLife Portal - Authentication Module

Handles login, logout, session management, and CSRF protection.
"""

import secrets
from datetime import datetime, timedelta
from functools import wraps

from flask import (
    Blueprint, render_template, request, redirect, url_for,
    flash, session, g, make_response
)
from flask_wtf import FlaskForm
from wtforms import StringField, PasswordField, BooleanField
from wtforms.validators import DataRequired, Length

from . import db, csrf
from .models import User, Session, Role
from .crypto import generate_secure_token, hash_session_id
from .audit import audit


auth_bp = Blueprint('auth', __name__)


class LoginForm(FlaskForm):
    """Login form with CSRF protection."""

    username = StringField('Emri i përdoruesit', validators=[
        DataRequired(message='Emri i përdoruesit është i detyrueshëm.'),
        Length(min=3, max=80, message='Emri duhet të jetë 3-80 karaktere.')
    ])
    password = PasswordField('Fjalëkalimi', validators=[
        DataRequired(message='Fjalëkalimi është i detyrueshëm.')
    ])
    remember_me = BooleanField('Mbamë mend')
    submit = StringField('Hyr')


def login_required(f):
    """
    Decorator to require authentication for a route.

    Checks for valid session and loads current user.
    """
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not g.current_user:
            flash('Duhet të jeni të autentifikuar për të aksesuar këtë faqe.', 'warning')
            return redirect(url_for('auth.login', next=request.path))
        return f(*args, **kwargs)
    return decorated_function


def load_user_from_session():
    """
    Load the current user from the session.

    Called automatically before each request.
    """
    g.current_user = None

    session_id = session.get('session_id')
    if not session_id:
        return

    # Look up session in database
    session_hash = hash_session_id(session_id)
    db_session = Session.query.filter_by(
        session_hash=session_hash,
        is_valid=True
    ).first()

    if not db_session:
        session.clear()
        return

    # Check expiration
    if db_session.is_expired():
        db_session.is_valid = False
        db.session.commit()
        session.clear()
        return

    # Load user
    user = User.query.filter_by(id=db_session.user_id, active=True).first()
    if user:
        # Update last seen
        db_session.touch()
        db.session.commit()
        g.current_user = user


def create_session_for_user(user: User, remember: bool = False) -> str:
    """
    Create a new server-side session for a user.

    Args:
        user: The user to create session for
        remember: If True, extend session lifetime

    Returns:
        The session ID (to be stored in cookie)
    """
    from .config import Config

    # Generate random session ID
    session_id = generate_secure_token(32)
    session_hash = hash_session_id(session_id)

    # Calculate expiration
    idle_timeout = Config.SESSION_IDLE_MINUTES
    absolute_hours = Config.SESSION_ABSOLUTE_HOURS

    if remember:
        absolute_hours = 24 * 7  # 1 week for remember me

    expires_at = datetime.utcnow() + timedelta(hours=absolute_hours)

    # Create session record
    db_session = Session(
        session_hash=session_hash,
        user_id=user.id,
        created_at=datetime.utcnow(),
        last_seen_at=datetime.utcnow(),
        expires_at=expires_at,
        is_valid=True,
        ip_address=request.remote_addr,
        user_agent=request.headers.get('User-Agent', '')[:512]
    )

    db.session.add(db_session)
    db.session.commit()

    return session_id


def invalidate_session(session_id: str):
    """
    Invalidate a session (logout).

    Args:
        session_id: The session ID to invalidate
    """
    session_hash = hash_session_id(session_id)
    db_session = Session.query.filter_by(session_hash=session_hash).first()

    if db_session:
        db_session.is_valid = False
        db.session.commit()


def rotate_session(old_session_id: str, user: User) -> str:
    """
    Rotate session ID (for privilege changes).

    Invalidates old session and creates new one.

    Args:
        old_session_id: The old session ID
        user: The user to create new session for

    Returns:
        New session ID
    """
    # Invalidate old session
    invalidate_session(old_session_id)

    # Create new session
    return create_session_for_user(user)


@auth_bp.before_app_request
def before_request():
    """Load user from session before each request."""
    load_user_from_session()


@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    """Handle user login."""
    from flask import session as flask_session

    # Redirect if already logged in
    if g.current_user:
        return redirect(url_for('index'))

    form = LoginForm()

    if form.validate_on_submit():
        username = form.username.data
        password = form.password.data
        remember = form.remember_me.data

        # Find user
        user = User.query.filter_by(username=username).first()

        if user and user.check_password(password):
            if not user.active:
                flash('Llogaria juaj është e bllokuar. Kontaktoni administratorin.', 'error')
                audit.log_login(user, success=False)
                return render_template('auth/login.html', form=form)

            # Create session
            session_id = create_session_for_user(user, remember=remember)
            flask_session['session_id'] = session_id

            # Update last login
            user.last_login_at = datetime.utcnow()
            db.session.commit()

            # Audit log
            audit.log_login(user, success=True)

            # Redirect to next or dashboard
            next_page = request.args.get('next')
            if next_page:
                return redirect(next_page)

            # Redirect based on role
            role_dashboards = {
                Role.ADMIN: 'admin.dashboard',
                Role.DOCTOR: 'doctor.dashboard',
                Role.RECEPTION: 'reception.dashboard',
                Role.PATIENT: 'patient.dashboard'
            }
            dashboard = role_dashboards.get(user.role, 'patient.dashboard')
            return redirect(url_for(dashboard))
        else:
            # Failed login - don't reveal if user exists
            flash('Emri i përdoruesit ose fjalëkalimi është gabim.', 'error')
            audit.log_login(user, success=False)

    return render_template('auth/login.html', form=form)


@auth_bp.route('/logout')
def logout():
    """Handle user logout."""
    from flask import session as flask_session

    if g.current_user:
        # Audit log
        audit.log_logout(g.current_user)

        # Invalidate session
        session_id = flask_session.get('session_id')
        if session_id:
            invalidate_session(session_id)

    # Clear session
    flask_session.clear()

    flash('Jeni shkëputur me sukses.', 'info')
    return redirect(url_for('auth.login'))


@auth_bp.route('/session/refresh', methods=['POST'])
@login_required
def refresh_session():
    """
    Refresh the current session (extend idle timeout).

    Called via AJAX to keep session alive during activity.
    """
    from flask import session as flask_session, jsonify

    session_id = flask_session.get('session_id')
    if not session_id:
        return jsonify({'error': 'No session'}), 400

    session_hash = hash_session_id(session_id)
    db_session = Session.query.filter_by(
        session_hash=session_hash,
        is_valid=True
    ).first()

    if db_session and not db_session.is_expired():
        # Extend session
        db_session.last_seen_at = datetime.utcnow()
        db.session.commit()
        return jsonify({'status': 'ok'})

    return jsonify({'error': 'Session expired'}), 401
