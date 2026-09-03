#!/usr/bin/env python3
"""
MediLife Portal - Management CLI

Usage:
    python manage.py <command> [options]

Commands:
    genkey          Generate secure random keys for configuration
    initdb          Initialize the database schema
    bootstrap       Bootstrap the application with admin user
    runssl          Run the server with HTTPS (for demo)
"""

import sys
import os
import argparse
import secrets
import hashlib

# Add app to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app import create_app, db
from app.models import User, Role


def generate_key():
    """Generate a secure random key."""
    return secrets.token_urlsafe(32)


def cmd_genkey(args):
    """Generate secure random keys for configuration."""
    print("# MediLife Portal - Generated Security Keys")
    print("# Add these to your environment or .env file\n")
    print(f"FLASK_SECRET_KEY={generate_key()}")
    print(f"APP_DATA_KEY={generate_key()}")
    print("\n# For production, also set:")
    print("# DATABASE_URL=postgresql://user:password@localhost/medilife_portal")


def cmd_initdb(args):
    """Initialize the database schema."""
    app = create_app('development')

    with app.app_context():
        print("Initializing database...")

        # Create all tables
        db.create_all()
        print("✓ Database tables created")

        # Verify tables
        from sqlalchemy import inspect
        inspector = inspect(db.engine)
        tables = inspector.get_table_names()
        print(f"✓ Tables: {', '.join(tables)}")

        print("\nDatabase initialized successfully!")


def cmd_bootstrap(args):
    """Bootstrap the application with initial data."""
    app = create_app('development')

    with app.app_context():
        # Create admin user if specified
        if args.admin_password:
            # Check if admin already exists
            existing = User.query.filter_by(username='admin').first()
            if existing:
                print("⚠ Admin user already exists")
            else:
                admin = User(
                    username='admin',
                    role=Role.ADMIN,
                    active=True
                )
                admin.set_password(args.admin_password)
                db.session.add(admin)
                db.session.commit()
                print("✓ Admin user created (username: admin)")

        # Create sample data if requested
        if args.sample_data:
            create_sample_data()

        print("\nBootstrap complete!")


def create_sample_data():
    """Create sample data for testing."""
    # Check if data already exists
    if User.query.count() > 1:
        print("⚠ Sample data already exists, skipping")
        return

    # Create doctor user
    doctor = User(
        username='dr.smith',
        role=Role.DOCTOR,
        active=True
    )
    doctor.set_password('Doctor123!')
    db.session.add(doctor)

    # Create reception user
    reception = User(
        username='reception',
        role=Role.RECEPTION,
        active=True
    )
    reception.set_password('Reception123!')
    db.session.add(reception)

    # Create patient user
    patient_user = User(
        username='patient1',
        role=Role.PATIENT,
        active=True
    )
    patient_user.set_password('Patient123!')
    db.session.add(patient_user)

    db.session.commit()
    print("✓ Sample users created")

    # Create sample patient
    from app.models import Patient
    from datetime import date, timedelta

    sample_patient = Patient(
        first_name='John',
        last_name='Doe',
        date_of_birth=date(1985, 6, 15),
        gender='male',
        phone='+355 69 123 4567',
        email='john.doe@example.com',
        address='Rruga e Durrësit, Tiranë',
        insurance_number='INS123456789',
        assigned_doctor_id=doctor.id,
        user_id=patient_user.id
    )
    db.session.add(sample_patient)
    db.session.commit()
    print("✓ Sample patient created")


def cmd_runssl(args):
    """Run the server with HTTPS for demo purposes."""
    from flask import Flask

    cert_file = args.cert
    key_file = args.key
    port = args.port

    if not os.path.exists(cert_file):
        print(f"Error: Certificate file not found: {cert_file}")
        sys.exit(1)

    if not os.path.exists(key_file):
        print(f"Error: Key file not found: {key_file}")
        sys.exit(1)

    app = create_app('development')

    print(f"\n🔒 Starting HTTPS server on port {port}")
    print(f"   Certificate: {cert_file}")
    print(f"   Key: {key_file}")
    print(f"\n   Access: https://localhost:{port}")
    print("   (Press Ctrl+C to stop)\n")

    # Note: For production, use a proper WSGI server like gunicorn
    app.run(
        host='0.0.0.0',
        port=port,
        ssl_context=(cert_file, key_file),
        debug=True
    )


def main():
    parser = argparse.ArgumentParser(description='MediLife Portal Management CLI')
    subparsers = parser.add_subparsers(dest='command', help='Available commands')

    # genkey command
    genkey_parser = subparsers.add_parser('genkey', help='Generate secure random keys')
    genkey_parser.set_defaults(func=cmd_genkey)

    # initdb command
    initdb_parser = subparsers.add_parser('initdb', help='Initialize the database')
    initdb_parser.set_defaults(func=cmd_initdb)

    # bootstrap command
    bootstrap_parser = subparsers.add_parser('bootstrap', help='Bootstrap the application')
    bootstrap_parser.add_argument('--admin-password', required=False, help='Admin user password')
    bootstrap_parser.add_argument('--sample-data', action='store_true', help='Create sample data')
    bootstrap_parser.set_defaults(func=cmd_bootstrap)

    # runssl command
    runssl_parser = subparsers.add_parser('runssl', help='Run HTTPS server for demo')
    runssl_parser.add_argument('--cert', required=True, help='Path to SSL certificate')
    runssl_parser.add_argument('--key', required=True, help='Path to SSL key')
    runssl_parser.add_argument('--port', type=int, default=443, help='Port number')
    runssl_parser.set_defaults(func=cmd_runssl)

    args = parser.parse_args()

    if args.command is None:
        parser.print_help()
        sys.exit(1)

    args.func(args)


if __name__ == '__main__':
    main()
