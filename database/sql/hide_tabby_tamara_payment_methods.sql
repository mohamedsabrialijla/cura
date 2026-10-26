-- إخفاء تابي وتمارا من طرق الدفع في المتجر
UPDATE payment_methods
SET status = 'inactive',
    updated_at = NOW()
WHERE LOWER(code) IN ('tabby', 'tamara')
   OR LOWER(COALESCE(title_en, '')) LIKE '%tabby%'
   OR LOWER(COALESCE(title_en, '')) LIKE '%tamara%'
   OR COALESCE(title_ar, '') LIKE '%تابي%'
   OR COALESCE(title_ar, '') LIKE '%تمارا%';
