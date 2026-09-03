/**
 * MediLife Portal - Main JavaScript
 *
 * Handles session refresh, CSRF token management, and UI interactions.
 */

(function() {
    'use strict';

    // CSRF Token handling for AJAX requests
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        // Fallback: look for token in forms
        const form = document.querySelector('form');
        if (form) {
            const tokenInput = form.querySelector('input[name="csrf_token"]');
            if (tokenInput) {
                return tokenInput.value;
            }
        }
        return null;
    }

    // Session refresh to prevent idle timeout
    function refreshSession() {
        const sessionId = getSessionId();
        if (!sessionId) return;

        fetch('/session/refresh', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRFToken': getCsrfToken()
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.status === 401) {
                // Session expired, redirect to login
                window.location.href = '/login?session_expired=1';
            }
        })
        .catch(error => {
            console.error('Session refresh failed:', error);
        });
    }

    // Get session ID from cookie
    function getSessionId() {
        const match = document.cookie.match(/session=([^;]+)/);
        return match ? match[1] : null;
    }

    // Initialize session refresh interval
    // Refresh every 5 minutes to prevent idle timeout (default idle is 15 min)
    function initSessionRefresh() {
        const isLoggedIn = document.querySelector('.sidebar') !== null;
        if (isLoggedIn) {
            setInterval(refreshSession, 5 * 60 * 1000);
        }
    }

    // Form validation helpers
    function validatePasswordStrength(password) {
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        const passed = Object.values(checks).filter(Boolean).length;
        return {
            passed,
            total: 5,
            strength: passed >= 4 ? 'strong' : passed >= 3 ? 'medium' : 'weak',
            checks
        };
    }

    // Update password strength indicator
    function initPasswordStrengthIndicator() {
        const passwordInputs = document.querySelectorAll('input[type="password"][name="password"], input[type="password"][name="new_password"]');

        passwordInputs.forEach(input => {
            const formGroup = input.closest('.form-group');
            if (!formGroup) return;

            // Create strength indicator
            const indicator = document.createElement('div');
            indicator.className = 'password-strength';
            indicator.innerHTML = `
                <div class="strength-bar">
                    <div class="strength-fill" data-strength=""></div>
                </div>
                <span class="strength-text"></span>
            `;
            formGroup.appendChild(indicator);

            input.addEventListener('input', () => {
                const result = validatePasswordStrength(input.value);
                const fill = indicator.querySelector('.strength-fill');
                const text = indicator.querySelector('.strength-text');

                fill.setAttribute('data-strength', result.strength);

                const widths = { weak: '33%', medium: '66%', strong: '100%' };
                const colors = { weak: '#ef4444', medium: '#f59e0b', strong: '#10b981' };

                fill.style.width = result.passed === 0 ? '0' : widths[result.strength];
                fill.style.backgroundColor = colors[result.strength];
                text.textContent = result.strength === 'strong' ? 'Fjalëkalim i fortë' :
                                   result.strength === 'medium' ? 'Fjalëkalim mesatar' : 'Fjalëkalim i dobët';
            });
        });
    }

    // Confirm delete actions
    function initConfirmDeletes() {
        document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!confirm('Jeni i sigurt që doni të kryeni këtë veprim?')) {
                    e.preventDefault();
                }
            });
        });
    }

    // Auto-hide flash messages
    function initFlashMessages() {
        const flashes = document.querySelectorAll('.flash');
        flashes.forEach(flash => {
            setTimeout(() => {
                flash.style.opacity = '0';
                flash.style.transition = 'opacity 0.5s ease';
                setTimeout(() => flash.remove(), 500);
            }, 5000);
        });
    }

    // Table row highlight on hover
    function initTableHighlight() {
        document.querySelectorAll('.data-table tbody tr').forEach(row => {
            row.addEventListener('click', () => {
                document.querySelectorAll('.data-table tbody tr').forEach(r => r.classList.remove('selected'));
                row.classList.add('selected');
            });
        });
    }

    // Initialize everything when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        initSessionRefresh();
        initPasswordStrengthIndicator();
        initConfirmDeletes();
        initFlashMessages();
        initTableHighlight();
    });

    // Export for external use
    window.MediLife = {
        refreshSession,
        getCsrfToken,
        validatePasswordStrength
    };

})();
