"""
MediLife Portal - Test Configuration and Fixtures
"""

import pytest
import os
from datetime import datetime, timedelta

# Set test environment before importing app
os.environ['FLASK_ENV'] = 'testing'
os.environ['TEST_DATABASE_URL'] = 'postgresql://medilife:medilife@localhost:5432/medilife_test'

from app import create_app, db
from app.models import User, Patient, Role, Session
from app.crypto import init_encryption


@pytest.fixture(scope='session')
def app():
    """Create application for testing."""
    app = create_app('testing')
    app.config['TESTING'] = True
    app.config['WTF_CSRF_ENABLED'] = True
    app.config['SQLALCHEMY_DATABASE_URI'] = os.environ['TEST_DATABASE_URL']

    with app.app_context():
        # Create tables
        db.create_all()

        # Initialize encryption
        init_encryption('test-encryption-key-for-testing-only')

        yield app

        # Cleanup
        db.session.remove()
        db.drop_all()


@pytest.fixture(scope='function')
def client(app):
    """Create test client."""
    return app.test_client()


@pytest.fixture(scope='function')
def runner(app):
    """Create CLI runner."""
    return app.test_cli_runner()


@pytest.fixture(scope='function')
def db_session(app):
    """Create database session for testing."""
    with app.app_context():
        # Clean database before each test
        db.session.rollback()

        # Clear all tables
        for table in reversed(db.metadata.sorted_tables):
            db.session.execute(table.delete())

        db.session.commit()

        yield db.session

        # Cleanup after test
        db.session.remove()


@pytest.fixture
def admin_user(db_session):
    """Create admin user fixture."""
    user = User(
        username='testadmin',
        role=Role.ADMIN,
        active=True
    )
    user.set_password('AdminPassword123!')
    db_session.add(user)
    db_session.commit()
    return user


@pytest.fixture
def doctor_user(db_session):
    """Create doctor user fixture."""
    user = User(
        username='testdoctor',
        role=Role.DOCTOR,
        active=True
    )
    user.set_password('DoctorPassword123!')
    db_session.add(user)
    db_session.commit()
    return user


@pytest.fixture
def reception_user(db_session):
    """Create reception user fixture."""
    user = User(
        username='testreception',
        role=Role.RECEPTION,
        active=True
    )
    user.set_password('ReceptionPassword123!')
    db_session.add(user)
    db_session.commit()
    return user


@pytest.fixture
def patient_user(db_session):
    """Create patient user fixture."""
    user = User(
        username='testpatient',
        role=Role.PATIENT,
        active=True
    )
    user.set_password('PatientPassword123!')
    db_session.add(user)
    db_session.commit()
    return user


@pytest.fixture
def sample_patient(db_session, doctor_user):
    """Create sample patient fixture."""
    patient = Patient(
        first_name='Test',
        last_name='Patient',
        date_of_birth=datetime(1990, 1, 1).date(),
        gender='male',
        phone='123456789',
        email='test@example.com',
        assigned_doctor_id=doctor_user.id
    )
    db_session.add(patient)
    db_session.commit()
    return patient


@pytest.fixture
def auth_headers(client, admin_user):
    """Create authenticated client with admin user."""
    with client.session_transaction() as sess:
        from app.crypto import generate_secure_token, hash_session_id
        from app.models import Session as DbSession
        from datetime import datetime, timedelta

        session_id = generate_secure_token(32)
        session_hash = hash_session_id(session_id)

        db_session = DbSession(
            session_hash=session_hash,
            user_id=admin_user.id,
            created_at=datetime.utcnow(),
            last_seen_at=datetime.utcnow(),
            expires_at=datetime.utcnow() + timedelta(hours=8),
            is_valid=True
        )
        db.session.add(db_session)
        db.session.commit()

        sess['session_id'] = session_id

    return client
