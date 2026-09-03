"""
MediLife Portal - Application Factory

Secure Flask application with PostgreSQL, RBAC, and audit logging.
"""

import os
import logging
from logging.handlers import RotatingFileHandler
from datetime import datetime

from flask import Flask, request, g
from flask_sqlalchemy import SQLAlchemy
from flask_wtf.csrf import CSRFProtect
from flask_session import Session
from flask_migrate import Migrate

from .config import config
from .crypto import init_encryption
from .audit import AuditLogger


# Initialize extensions
db = SQLAlchemy()
csrf = CSRFProtect()
session = Session()
migrate = Migrate()
audit_logger = AuditLogger()


def create_app(config_name=None):
    """
    Application factory for creating the Flask app.

    Args:
        config_name: Configuration name ('development', 'production', 'testing')

    Returns:
        Configured Flask application instance
    """
    if config_name is None:
        config_name = os.environ.get('FLASK_ENV', 'default')

    app = Flask(__name__)
    app.config.from_object(config[config_name])

    # Initialize extensions
    db.init_app(app)
    csrf.init_app(app)
    session.init_app(app)
    migrate.init_app(app, db)
    audit_logger.init_app(app)

    # Initialize encryption
    init_encryption(app.config['APP_DATA_KEY'])

    # Configure logging
    setup_logging(app)

    # Register security headers
    @app.after_request
    def add_security_headers(response):
        """Add security headers to all responses."""
        for header, value in app.config['SECURITY_HEADERS'].items():
            response.headers[header] = value
        return response

    # Register blueprints
    from .routes.admin import admin_bp
    from .routes.doctor import doctor_bp
    from .routes.reception import reception_bp
    from .routes.patient import patient_bp
    from .auth import auth_bp

    app.register_blueprint(auth_bp)
    app.register_blueprint(admin_bp, url_prefix='/admin')
    app.register_blueprint(doctor_bp, url_prefix='/doctor')
    app.register_blueprint(reception_bp, url_prefix='/reception')
    app.register_blueprint(patient_bp, url_prefix='/patient')

    # Main dashboard route based on role
    @app.route('/')
    def index():
        """Redirect to appropriate dashboard based on user role."""
        from .auth import current_user, login_required
        from flask import redirect, url_for

        if not current_user.is_authenticated:
            return redirect(url_for('auth.login'))

        role_dashboards = {
            'admin': 'admin.dashboard',
            'doctor': 'doctor.dashboard',
            'reception': 'reception.dashboard',
            'patient': 'patient.dashboard'
        }

        dashboard = role_dashboards.get(current_user.role, 'patient.dashboard')
        return redirect(url_for(dashboard))

    # Error handlers
    @app.errorhandler(404)
    def not_found_error(error):
        return render_error_page(app, '404 Not Found', 404)

    @app.errorhandler(403)
    def forbidden_error(error):
        return render_error_page(app, '403 Forbidden', 403)

    @app.errorhandler(500)
    def internal_error(error):
        db.session.rollback()
        return render_error_page(app, '500 Internal Server Error', 500)

    return app


def setup_logging(app):
    """Configure application logging."""
    # Create logs directory
    log_dir = os.path.dirname(app.config['AUDIT_LOG_FILE'])
    if log_dir and not os.path.exists(log_dir):
        os.makedirs(log_dir, exist_ok=True)

    # Rotating file handler
    file_handler = RotatingFileHandler(
        app.config['AUDIT_LOG_FILE'],
        maxBytes=10 * 1024 * 1024,  # 10MB
        backupCount=10
    )
    file_handler.setFormatter(logging.Formatter(
        '%(asctime)s %(levelname)s: %(message)s [in %(pathname)s:%(lineno)d]'
    ))
    file_handler.setLevel(logging.INFO)

    # Console handler
    console_handler = logging.StreamHandler()
    console_handler.setFormatter(logging.Formatter(
        '%(asctime)s %(levelname)s: %(message)s'
    ))
    console_handler.setLevel(logging.INFO)

    # Application logger
    app.logger.addHandler(file_handler)
    if app.config.get('AUDIT_TO_CONSOLE'):
        app.logger.addHandler(console_handler)
    app.logger.setLevel(logging.INFO)

    app.logger.info('MediLife Portal startup')


def render_error_page(app, message, status_code):
    """Render error page with security headers."""
    from flask import render_template, make_response

    # Try to render custom error template
    try:
        response = make_response(
            render_template('error.html', message=message, status_code=status_code),
            status_code
        )
    except Exception:
        response = make_response(f'<h1>{message}</h1>', status_code)

    # Add security headers
    for header, value in app.config['SECURITY_HEADERS'].items():
        response.headers[header] = value

    return response
