-- MediLife Portal - Audit Table Enhancements
-- Additional indexes and constraints for audit logging

-- Add composite index for common audit queries
CREATE INDEX IF NOT EXISTS idx_audit_events_actor_timestamp
    ON audit_events(actor_id, timestamp DESC);

CREATE INDEX IF NOT EXISTS idx_audit_events_target
    ON audit_events(target_type, target_id);

-- Add check constraint for outcome values
ALTER TABLE audit_events
    ADD CONSTRAINT chk_outcome CHECK (outcome IN ('success', 'failure'));

-- Create view for recent security events
CREATE OR REPLACE VIEW recent_security_events AS
SELECT
    ae.id,
    ae.action,
    ae.outcome,
    ae.timestamp,
    ae.source_ip,
    u.username as actor_username,
    u.role as actor_role
FROM audit_events ae
LEFT JOIN users u ON ae.actor_id = u.id
WHERE ae.timestamp > NOW() - INTERVAL '24 hours'
ORDER BY ae.timestamp DESC
LIMIT 100;

-- Create view for failed login attempts
CREATE OR REPLACE VIEW failed_login_attempts AS
SELECT
    ae.id,
    ae.timestamp,
    ae.source_ip,
    ae.details->>'username' as attempted_username,
    COUNT(*) OVER (PARTITION BY ae.source_ip, DATE(ae.timestamp)) as attempts_from_ip
FROM audit_events ae
WHERE ae.action = 'LOGIN' AND ae.outcome = 'failure'
ORDER BY ae.timestamp DESC;
