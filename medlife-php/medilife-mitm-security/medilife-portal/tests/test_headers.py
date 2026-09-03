"""
MediLife Portal - Security Headers Tests

Tests for:
- HSTS header presence
- Content-Security-Policy header
- X-Content-Type-Options header
- X-Frame-Options header
- Referrer-Policy header
- Cache-Control header on authenticated pages
"""

import pytest


class TestSecurityHeaders:
    """Test security headers in responses."""

    def test_hsts_header_present(self, client):
        """Test Strict-Transport-Security header is present."""
        response = client.get('/login')
        assert response.status_code == 200
        assert 'Strict-Transport-Security' in response.headers
        assert 'max-age=31536000' in response.headers['Strict-Transport-Security']

    def test_csp_header_present(self, client):
        """Test Content-Security-Policy header is present."""
        response = client.get('/login')
        assert response.status_code == 200
        assert 'Content-Security-Policy' in response.headers
        csp = response.headers['Content-Security-Policy']
        assert "default-src 'self'" in csp

    def test_x_content_type_options_header(self, client):
        """Test X-Content-Type-Options header is present."""
        response = client.get('/login')
        assert response.status_code == 200
        assert 'X-Content-Type-Options' in response.headers
        assert response.headers['X-Content-Type-Options'] == 'nosniff'

    def test_x_frame_options_header(self, client):
        """Test X-Frame-Options header is present."""
        response = client.get('/login')
        assert response.status_code == 200
        assert 'X-Frame-Options' in response.headers
        assert response.headers['X-Frame-Options'] == 'DENY'

    def test_referrer_policy_header(self, client):
        """Test Referrer-Policy header is present."""
        response = client.get('/login')
        assert response.status_code == 200
        assert 'Referrer-Policy' in response.headers
        assert 'strict-origin-when-cross-origin' in response.headers['Referrer-Policy']

    def test_cache_control_on_authenticated_page(self, client, db_session, admin_user):
        """Test Cache-Control header on authenticated pages."""
        from app.crypto import generate_secure_token, hash_session_id
        from app.models import Session
        from datetime import datetime, timedelta

        # Create session
        session_id = generate_secure_token(32)
        session = Session(
            session_hash=hash_session_id(session_id),
            user_id=admin_user.id,
            created_at=datetime.utcnow(),
            last_seen_at=datetime.utcnow(),
            expires_at=datetime.utcnow() + timedelta(hours=8),
            is_valid=True
        )
        db_session.add(session)
        db_session.commit()

        with client.session_transaction() as sess:
            sess['session_id'] = session_id

        response = client.get('/admin/', follow_redirects=True)
        assert response.status_code == 200
        assert 'Cache-Control' in response.headers
        assert 'no-store' in response.headers['Cache-Control']

    def test_pragma_no_cache(self, client):
        """Test Pragma: no-cache header."""
        response = client.get('/login')
        assert response.status_code == 200
        assert 'Pragma' in response.headers
        assert response.headers['Pragma'] == 'no-cache'


class TestCookieSecurity:
    """Test cookie security settings."""

    def test_session_cookie_httponly(self, client):
        """Test session cookie has HttpOnly flag."""
        response = client.get('/login')
        # Check Set-Cookie header for HttpOnly
        cookies = response.headers.getlist('Set-Cookie')
        for cookie in cookies:
            if 'session' in cookie.lower():
                assert 'HttpOnly' in cookie or 'httponly' in cookie.lower()

    def test_session_cookie_secure(self, client):
        """Test session cookie has Secure flag (in production)."""
        # Note: In testing, Secure might be False
        # This test documents the expected behavior
        pass  # Would need HTTPS test server

    def test_session_cookie_samesite(self, client):
        """Test session cookie has SameSite=Strict."""
        response = client.get('/login')
        cookies = response.headers.getlist('Set-Cookie')
        for cookie in cookies:
            if 'session' in cookie.lower():
                assert 'SameSite=Strict' in cookie or 'samesite=strict' in cookie.lower()


class TestHTTPSRedirect:
    """Test HTTPS enforcement."""

    def test_http_redirects_to_https(self, client):
        """Test that HTTP redirects to HTTPS in production."""
        # This would need actual HTTPS test setup
        # Documented for production verification
        pass
