-- Insert User untuk testing Google Login
-- Jalankan di MySQL/phpMyAdmin

INSERT INTO users (
    id, 
    name, 
    email, 
    type, 
    institution,
    phone, 
    line,
    created_at, 
    updated_at
) VALUES (
    UUID(), 
    'Ezra Desmond', 
    'c14240176@john.petra.ac.id', 
    'INTERNAL',
    'Universitas Kristen Petra',
    '081234567890',
    'ezradesmond',
    NOW(), 
    NOW()
);
