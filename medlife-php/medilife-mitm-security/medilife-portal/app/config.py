"""
MediLife Portal - Security Configuration

Configuration settings for the Flask application with security-first defaults.
All sensitive values should be provided via environment variables.
"""

import os
from datetime import timedelta


class Config:
    """Base configuration with security defaults."""

    # Secret key for session signing and CSRF protection
    # MUST be set via environment variable in production
    SECRET_KEY = os.environ.get('FLASK_SECRET_KEY', 'dev-key-change-in-production')

    # Database configuration (PostgreSQL only - no SQLite)
    DATABASE_URL = os.environ.get(
        'DATABASE_URL',
        'postgresql://medilife:medilife@localhost:5432/medilife_portal'
    )

    # Disable SQLAlchemy event tracking (performance)
    SQLALCHEMY_TRACK_MODIFICATIONS = False

    # Session configuration
    SESSION_TYPE = 'filesystem'  # Can be 'redis' in production
    SESSION_PERMANENT = True
    SESSION_USE_SIGNER = True
    SESSION_KEY_PREFIX = 'medilife_session:'

    # Session timeouts (from environment variables)
    SESSION_IDLE_MINUTES = int(os.environ.get('SESSION_IDLE_MINUTES', '15'))
    SESSION_ABSOLUTE_HOURS = int(os.environ.get('SESSION_ABSOLUTE_HOURS', '8'))

    # Encryption key for sensitive patient data (AES-256-GCM)
    # MUST be set via environment variable in production
    APP_DATA_KEY = os.environ.get('APP_DATA_KEY', 'dev-encryption-key-change-me')

    # Security headers
    SECURITY_HEADERS = {
        'Strict-Transport-Security': 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy': "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'",
        'X-Content-Type-Options': 'nosniff',
        'X-Frame-Options': 'DENY',
        'X-XSS-Protection': '1; mode=block',
        'Referrer-Policy': 'strict-origin-when-cross-origin',
        'Cache-Control': 'no-store, no-cache, must-revalidate',
        'Pragma': 'no-cache'
    }

    # Argon2id password hashing parameters
    ARGON2_TIME_COST = 3  # Number of iterations
    ARGON2_MEMORY_COST = 65536  # Memory in KiB
    ARGON2_PARALLELISM = 4  # Parallel threads

    # Audit logging
    AUDIT_LOG_FILE = os.environ.get('AUDIT_LOG_FILE', '/var/log/medilife/audit.log')
    AUDIT_TO_CONSOLE = os.environ.get('AUDIT_TO_CONSOLE', 'false').lower() == 'true'

    # HTTPS only (for production)
    SESSION_COOKIE_SECURE = True
    SESSION_COOKIE_HTTPONLY = True
    SESSION_COOKIE_SAMESITE = 'Strict'

    # CSRF configuration
    WTF_CSRF_ENABLED = True
    WTF_CSRF_TIME_LIMIT = 3600  # 1 hour
    WTF_CSRF_SSL_STRICT = True


class DevelopmentConfig(Config):
    """Development configuration with relaxed security for testing."""

    DEBUG = True
    SESSION_COOKIE_SECURE = False  # Allow HTTP in development
    SQLALCHEMY_ECHO = True  # Log SQL queries for debugging


class ProductionConfig(Config):
    """Production configuration with strict security settings."""

    DEBUG = False
    SQLALCHEMY_ECHO = False

    # Require environment variables in production
    def __init__(self):
        if self.SECRET_KEY == 'dev-key-change-in-production':
            raise ValueError("FLASK_SECRET_KEY must be set in production")
        if self.APP_DATA_KEY == 'dev-encryption-key-change-me':
            raise ValueError("APP_DATA_KEY must be set in production")


class TestingConfig(Config):
    """Testing configuration for automated tests."""

    TESTING = True
    SQLALCHEMY_DATABASE_URI = os.environ.get(
        'TEST_DATABASE_URL',
        'postgresql://medilife:medilife@localhost:5432/medilife_test'
    )
    SESSION_COOKIE_SECURE = False
    WTF_CSRF_ENABLED = True  # Keep CSRF enabled for tests
    ARGON2_TIME_COST = 1  # Faster hashing for tests
    ARGON2_MEMORY_COST = 8192


# Configuration selector
config = {
    'development': DevelopmentConfig,
    'production': ProductionConfig,
    'testing': TestingConfig,
    'default': DevelopmentConfig
}
