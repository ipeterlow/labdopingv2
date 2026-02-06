-- ============================================
-- ÍNDICES DE RENDIMIENTO PARA LABDOPING
-- Ejecutar en MySQL/phpMyAdmin
-- ============================================

-- IMPORTANTE: Estos comandos usan IF NOT EXISTS para evitar errores
-- si algún índice ya existe. Son seguros para producción.

-- ============================================
-- ÍNDICES PARA TABLA: samples
-- ============================================

-- Índice compuesto para joins y filtros por empresa/estado
ALTER TABLE `samples` 
ADD INDEX `idx_samples_company_status` (`company_id`, `status`);

-- Índice para ordenamiento por ID con soft deletes
ALTER TABLE `samples` 
ADD INDEX `idx_samples_id_deleted` (`id`, `deleted_at`);

-- Índice para filtro por tipo de muestra
ALTER TABLE `samples` 
ADD INDEX `idx_samples_type` (`type`);

-- Índice para reception_id (usado en edición de grupos)
ALTER TABLE `samples` 
ADD INDEX `idx_samples_reception_id` (`reception_id`);

-- Índice para búsquedas por external_id
ALTER TABLE `samples` 
ADD INDEX `idx_samples_external_id` (`external_id`);

-- Índice para búsquedas por internal_id
ALTER TABLE `samples` 
ADD INDEX `idx_samples_internal_id` (`internal_id`);

-- Índice para filtros de fecha recepción
ALTER TABLE `samples` 
ADD INDEX `idx_samples_received_at` (`received_at`);

-- Índice para filtros de fecha análisis
ALTER TABLE `samples` 
ADD INDEX `idx_samples_analyzed_at` (`analyzed_at`);

-- ============================================
-- ÍNDICES PARA TABLA: characteristic_samples
-- ============================================

-- Índice para foreign key y soft deletes
ALTER TABLE `characteristic_samples` 
ADD INDEX `idx_char_samples_sample_deleted` (`sample_id`, `deleted_at`);

-- ============================================
-- ÍNDICES PARA TABLA: documents
-- ============================================

-- Índice compuesto para búsquedas por sample y tipo de documento
ALTER TABLE `documents` 
ADD INDEX `idx_documents_sample_type` (`sample_id`, `type_document`);

-- ============================================
-- ÍNDICES PARA TABLA: companies
-- ============================================

-- Índice para ordenamiento por nombre
ALTER TABLE `companies` 
ADD INDEX `idx_companies_name` (`name`);
