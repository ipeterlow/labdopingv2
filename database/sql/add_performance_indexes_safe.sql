-- ============================================
-- ÍNDICES DE RENDIMIENTO PARA LABDOPING
-- VERSIÓN SEGURA - Verifica antes de crear
-- Ejecutar en MySQL/phpMyAdmin
-- ============================================

-- ============================================
-- ÍNDICES PARA TABLA: samples
-- ============================================

-- Verificar y crear índice company_status
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'samples' 
               AND index_name = 'idx_samples_company_status');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `samples` ADD INDEX `idx_samples_company_status` (`company_id`, `status`)', 
    'SELECT ''Index idx_samples_company_status already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y crear índice id_deleted
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'samples' 
               AND index_name = 'idx_samples_id_deleted');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `samples` ADD INDEX `idx_samples_id_deleted` (`id`, `deleted_at`)', 
    'SELECT ''Index idx_samples_id_deleted already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y crear índice type
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'samples' 
               AND index_name = 'idx_samples_type');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `samples` ADD INDEX `idx_samples_type` (`type`)', 
    'SELECT ''Index idx_samples_type already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y crear índice reception_id
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'samples' 
               AND index_name = 'idx_samples_reception_id');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `samples` ADD INDEX `idx_samples_reception_id` (`reception_id`)', 
    'SELECT ''Index idx_samples_reception_id already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y crear índice external_id
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'samples' 
               AND index_name = 'idx_samples_external_id');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `samples` ADD INDEX `idx_samples_external_id` (`external_id`)', 
    'SELECT ''Index idx_samples_external_id already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y crear índice internal_id
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'samples' 
               AND index_name = 'idx_samples_internal_id');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `samples` ADD INDEX `idx_samples_internal_id` (`internal_id`)', 
    'SELECT ''Index idx_samples_internal_id already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y crear índice received_at
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'samples' 
               AND index_name = 'idx_samples_received_at');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `samples` ADD INDEX `idx_samples_received_at` (`received_at`)', 
    'SELECT ''Index idx_samples_received_at already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y crear índice analyzed_at
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'samples' 
               AND index_name = 'idx_samples_analyzed_at');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `samples` ADD INDEX `idx_samples_analyzed_at` (`analyzed_at`)', 
    'SELECT ''Index idx_samples_analyzed_at already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- ÍNDICES PARA TABLA: characteristic_samples
-- ============================================

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'characteristic_samples' 
               AND index_name = 'idx_char_samples_sample_deleted');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `characteristic_samples` ADD INDEX `idx_char_samples_sample_deleted` (`sample_id`, `deleted_at`)', 
    'SELECT ''Index idx_char_samples_sample_deleted already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- ÍNDICES PARA TABLA: documents
-- ============================================

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'documents' 
               AND index_name = 'idx_documents_sample_type');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `documents` ADD INDEX `idx_documents_sample_type` (`sample_id`, `type_document`)', 
    'SELECT ''Index idx_documents_sample_type already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- ÍNDICES PARA TABLA: companies
-- ============================================

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics 
               WHERE table_schema = DATABASE() 
               AND table_name = 'companies' 
               AND index_name = 'idx_companies_name');
SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE `companies` ADD INDEX `idx_companies_name` (`name`)', 
    'SELECT ''Index idx_companies_name already exists''');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- VERIFICACIÓN FINAL
-- ============================================
SELECT 'Índices creados exitosamente' AS resultado;

-- Para ver los índices creados:
-- SHOW INDEX FROM samples;
-- SHOW INDEX FROM characteristic_samples;
-- SHOW INDEX FROM documents;
-- SHOW INDEX FROM companies;
