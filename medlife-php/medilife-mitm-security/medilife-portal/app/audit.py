"""
MediLife Portal - Audit Logging

Security audit logging to PostgreSQL and rotating file.
"""

import logging
from logging.handlers import RotatingFileHandler
from datetime import datetime
from typing import Optional, Dict, Any

from flask import Flask, request, g, has_request_context
from sqlalchemy import text

from . import db


class AuditLogger:
    """
    Audit logger for security events.

    Logs to both PostgreSQL (audit_events table) and a rotating file.
    """

    def __init__(self):
        self.logger = None
        self.file_handler = None

    def init_app(self, app: Flask):
        """
        Initialize the audit logger with Flask app configuration.

        Args:
            app: Flask application instance
        """
        self.logger = logging.getLogger('medilife.audit')
        self.logger.setLevel(logging.INFO)
        self.logger.propagate = False

        # Clear existing handlers
        self.logger.handlers = []

        # File handler with rotation
        log_file = app.config.get('AUDIT_LOG_FILE', '/var/log/medilife/audit.log')
        log_dir = '/'.join(log_file.split('/')[:-1])

        if log_dir:
            import os
            if not os.path.exists(log_dir):
                os.makedirs(log_dir, exist_ok=True)

        self.file_handler = RotatingFileHandler(
            log_file,
            maxBytes=10 * 1024 * 1024,  # 10MB
            backupCount=10
        )
        self.file_handler.setFormatter(logging.Formatter(
            '%(asctime)s [AUDIT] %(levelname)s: %(message)s'
        ))
        self.file_handler.setLevel(logging.INFO)
        self.logger.addHandler(self.file_handler)

        # Store app reference for database access
        self.app = app

    def log(
        self,
        action: str,
        outcome: str,
        target_type: Optional[str] = None,
        target_id: Optional[int] = None,
        patient_id: Optional[int] = None,
        details: Optional[Dict[str, Any]] = None,
        actor_id: Optional[int] = None
    ):
        """
        Log an audit event to database and file.

        Args:
            action: The action performed (e.g., 'LOGIN', 'VIEW_RECORD', 'EDIT_PATIENT')
            outcome: 'success' or 'failure'
            target_type: Type of target (e.g., 'user', 'patient', 'record')
            target_id: ID of the target
            patient_id: Patient ID if applicable
            details: Additional details as dictionary
            actor_id: User ID who performed the action (defaults to current user)
        """
        from .models import AuditEvent, User

        # Get actor from context if not provided
        if actor_id is None and has_request_context():
            if hasattr(g, 'current_user') and g.current_user and hasattr(g.current_user, 'id'):
                actor_id = g.current_user.id

        # Get source IP
        source_ip = None
        if has_request_context():
            source_ip = request.remote_addr

        # Create audit event
        event = AuditEvent(
            actor_id=actor_id,
            action=action,
            target_type=target_type,
            target_id=target_id,
            timestamp=datetime.utcnow(),
            source_ip=source_ip,
            outcome=outcome,
            details=details,
            patient_id=patient_id
        )

        try:
            db.session.add(event)
            db.session.commit()
        except Exception as e:
            db.session.rollback()
            self.logger.error(f"Failed to write audit event to database: {e}")

        # Log to file
        log_message = (
            f"action={action} outcome={outcome} "
            f"actor_id={actor_id} target={target_type}:{target_id} "
            f"patient_id={patient_id} ip={source_ip}"
        )
        if details:
            log_message += f" details={details}"

        if outcome == 'success':
            self.logger.info(log_message)
        else:
            self.logger.warning(log_message)

    def log_login(self, user: 'User', success: bool, source_ip: Optional[str] = None):
        """Log a login attempt."""
        from .models import User

        actor_id = user.id if user else None

        # Log to database
        event = AuditEvent(
            actor_id=actor_id,
            action='LOGIN',
            target_type='user',
            target_id=actor_id,
            timestamp=datetime.utcnow(),
            source_ip=source_ip or (request.remote_addr if has_request_context() else None),
            outcome='success' if success else 'failure',
            details={'username': user.username if user else 'unknown'}
        )

        try:
            db.session.add(event)
            db.session.commit()
        except Exception:
            db.session.rollback()

        # Log to file
        status = 'successful' if success else 'failed'
        username = user.username if user else 'unknown'
        self.logger.info(f"LOGIN {status} for user={username} ip={event.source_ip}")

    def log_logout(self, user: 'User'):
        """Log a logout event."""
        self.log(
            action='LOGOUT',
            outcome='success',
            target_type='user',
            target_id=user.id,
            details={'username': user.username}
        )

    def log_record_access(self, patient_id: int, record_id: int, success: bool = True):
        """Log access to a patient record."""
        from .models import User

        actor = getattr(g, 'current_user', None)
        actor_id = actor.id if actor else None

        self.log(
            action='VIEW_RECORD',
            outcome='success' if success else 'failure',
            target_type='patient_record',
            target_id=record_id,
            patient_id=patient_id,
            actor_id=actor_id
        )

    def log_record_edit(self, patient_id: int, record_id: int, field: str):
        """Log editing of a patient record."""
        from .models import User

        actor = getattr(g, 'current_user', None)
        actor_id = actor.id if actor else None

        self.log(
            action='EDIT_RECORD',
            outcome='success',
            target_type='patient_record',
            target_id=record_id,
            patient_id=patient_id,
            details={'field': field},
            actor_id=actor_id
        )

    def log_admin_action(self, action: str, target_type: str, target_id: int, details: Optional[Dict] = None):
        """Log an administrative action."""
        from .models import User

        actor = getattr(g, 'current_user', None)
        actor_id = actor.id if actor else None

        self.log(
            action=f'ADMIN_{action}',
            outcome='success',
            target_type=target_type,
            target_id=target_id,
            details=details,
            actor_id=actor_id
        )

    def log_crud_action(
        self,
        action: str,
        target_type: str,
        target_id: int,
        success: bool = True,
        details: Optional[Dict] = None
    ):
        """Log a CRUD operation."""
        from .models import User

        actor = getattr(g, 'current_user', None)
        actor_id = actor.id if actor else None

        self.log(
            action=action,
            outcome='success' if success else 'failure',
            target_type=target_type,
            target_id=target_id,
            details=details,
            actor_id=actor_id
        )


# Global audit logger instance
audit = AuditLogger()
