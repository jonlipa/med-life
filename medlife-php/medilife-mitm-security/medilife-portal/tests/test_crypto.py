"""
MediLife Portal - Cryptography Tests

Tests for:
- AES-256-GCM encryption/decryption
- Round-trip encryption
- Ciphertext storage verification
- No plaintext in database
"""

import pytest

from app.crypto import encrypt_field, decrypt_field, init_encryption


class TestAES256GCM:
    """Test AES-256-GCM encryption."""

    def test_encryption_initialization(self):
        """Test encryption module initializes correctly."""
        init_encryption('test-key-for-initialization')
        # Should not raise exception

    def test_encrypt_decrypt_round_trip(self):
        """Test that encrypted data can be decrypted correctly."""
        init_encryption('test-encryption-key-32bytes-long!')

        plaintext = "This is sensitive medical data"

        ciphertext, nonce = encrypt_field(plaintext)
        decrypted = decrypt_field(ciphertext, nonce)

        assert decrypted == plaintext

    def test_ciphertext_different_from_plaintext(self):
        """Test that ciphertext is different from plaintext."""
        init_encryption('test-encryption-key-32bytes-long!')

        plaintext = "Sensitive diagnosis information"

        ciphertext, nonce = encrypt_field(plaintext)

        assert ciphertext != plaintext.encode('utf-8')
        assert isinstance(ciphertext, bytes)
        assert len(ciphertext) > len(plaintext)  # Includes auth tag

    def test_different_nonces_for_same_plaintext(self):
        """Test that same plaintext produces different ciphertexts."""
        init_encryption('test-encryption-key-32bytes-long!')

        plaintext = "Same medical data"

        ciphertext1, nonce1 = encrypt_field(plaintext)
        ciphertext2, nonce2 = encrypt_field(plaintext)

        # Nonces should be different (random)
        assert nonce1 != nonce2
        # Ciphertexts should be different due to different nonces
        assert ciphertext1 != ciphertext2

    def test_tampered_ciphertext_fails_decryption(self):
        """Test that tampered ciphertext fails authentication."""
        from cryptography.exceptions import InvalidTag

        init_encryption('test-encryption-key-32bytes-long!')

        plaintext = "Medical data"
        ciphertext, nonce = encrypt_field(plaintext)

        # Tamper with ciphertext
        tampered = bytearray(ciphertext)
        tampered[0] ^= 0xFF  # Flip bits

        with pytest.raises(InvalidTag):
            decrypt_field(bytes(tampered), nonce)

    def test_wrong_nonce_fails_decryption(self):
        """Test that wrong nonce fails decryption."""
        from cryptography.exceptions import InvalidTag

        init_encryption('test-encryption-key-32bytes-long!')

        plaintext = "Medical data"
        ciphertext, nonce = encrypt_field(plaintext)

        # Use wrong nonce
        wrong_nonce = b'\x00' * 12

        with pytest.raises(InvalidTag):
            decrypt_field(ciphertext, wrong_nonce)

    def test_empty_string_encryption(self):
        """Test encryption of empty string."""
        init_encryption('test-encryption-key-32bytes-long!')

        ciphertext, nonce = encrypt_field("")
        decrypted = decrypt_field(ciphertext, nonce)

        assert decrypted == ""

    def test_unicode_characters_encryption(self):
        """Test encryption of Unicode characters (Albanian text)."""
        init_encryption('test-encryption-key-32bytes-long!')

        plaintext = "Diagnoza: Pacienti ka dhimbje koke dhe të vjella"

        ciphertext, nonce = encrypt_field(plaintext)
        decrypted = decrypt_field(ciphertext, nonce)

        assert decrypted == plaintext

    def test_long_text_encryption(self):
        """Test encryption of long text."""
        init_encryption('test-encryption-key-32bytes-long!')

        plaintext = "A" * 10000  # 10KB text

        ciphertext, nonce = encrypt_field(plaintext)
        decrypted = decrypt_field(ciphertext, nonce)

        assert decrypted == plaintext


class TestSecureTokenGeneration:
    """Test secure token generation."""

    def test_generate_secure_token(self):
        """Test secure token generation."""
        from app.crypto import generate_secure_token

        token1 = generate_secure_token(32)
        token2 = generate_secure_token(32)

        assert len(token1) >= 32
        assert token1 != token2  # Should be unique

    def test_session_id_hashing(self):
        """Test session ID hashing for storage."""
        from app.crypto import hash_session_id

        session_id = "random_session_id_12345"
        hash1 = hash_session_id(session_id)
        hash2 = hash_session_id(session_id)

        # Hash should be consistent
        assert hash1 == hash2
        assert len(hash1) == 64  # SHA-256 hex length
