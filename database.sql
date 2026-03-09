-- =============================================
-- BASE DE DATOS: SISTEMA DE TUTORIAS
-- Ejecutar en phpMyAdmin o MySQL
-- =============================================

CREATE DATABASE IF NOT EXISTS tutorias_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tutorias_db;

-- ---------------------------------------------
-- TABLA: usuarios (estudiantes, tutores, admin)
-- ---------------------------------------------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('estudiante','tutor','admin') DEFAULT 'estudiante',
    carrera VARCHAR(150),
    semestre INT,
    foto VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------
-- TABLA: materias
-- ---------------------------------------------
CREATE TABLE materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(20),
    descripcion TEXT,
    activa TINYINT(1) DEFAULT 1
);

-- ---------------------------------------------
-- TABLA: tutor_materias (qué materias da cada tutor)
-- ---------------------------------------------
CREATE TABLE tutor_materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    materia_id INT NOT NULL,
    FOREIGN KEY (tutor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE
);

-- ---------------------------------------------
-- TABLA: horarios_tutor (disponibilidad del tutor)
-- ---------------------------------------------
CREATE TABLE horarios_tutor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    dia_semana ENUM('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    sala VARCHAR(100),
    modalidad ENUM('presencial','virtual','ambas') DEFAULT 'presencial',
    FOREIGN KEY (tutor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ---------------------------------------------
-- TABLA: tutorias (reservas)
-- ---------------------------------------------
CREATE TABLE tutorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    tutor_id INT NOT NULL,
    materia_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    modalidad ENUM('presencial','virtual') DEFAULT 'presencial',
    sala VARCHAR(100),
    notas TEXT,
    estado ENUM('pendiente','confirmada','completada','cancelada') DEFAULT 'pendiente',
    creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
    FOREIGN KEY (tutor_id) REFERENCES usuarios(id),
    FOREIGN KEY (materia_id) REFERENCES materias(id)
);

-- ---------------------------------------------
-- TABLA: comentarios (calificaciones y reseñas)
-- ---------------------------------------------
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutoria_id INT NOT NULL UNIQUE,
    estudiante_id INT NOT NULL,
    tutor_id INT NOT NULL,
    calificacion TINYINT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
    comentario TEXT,
    respuesta_tutor TEXT,
    respuesta_docente TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutoria_id) REFERENCES tutorias(id),
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
    FOREIGN KEY (tutor_id) REFERENCES usuarios(id)
);

-- =============================================
-- DATOS DE PRUEBA
-- =============================================

-- Admin (contraseña: admin123)
INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol) VALUES
('Admin', 'Sistema', 'admin@uts.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Materias
INSERT INTO materias (nombre, codigo) VALUES
('Cálculo I', 'MAT101'),
('Cálculo II', 'MAT102'),
('Álgebra Lineal', 'MAT201'),
('Programación I', 'SIS101'),
('Programación II', 'SIS102'),
('Algoritmos y Estructuras', 'SIS201'),
('Física I', 'FIS101'),
('Física II', 'FIS102'),
('Estadística', 'MAT301'),
('Base de Datos', 'SIS301'),
('Redes', 'SIS401'),
('Química General', 'QUI101');

-- Tutor 1 (contraseña: tutor123)
INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol, carrera, semestre) VALUES
('Carlos', 'Martínez', 'carlos@uts.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tutor', 'Ing. de Sistemas', 9),
('Laura', 'Rodríguez', 'laura@uts.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tutor', 'Matemáticas', 10),
('Jorge', 'Peña', 'jorge@uts.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tutor', 'Física', 8),
('Valentina', 'García', 'valentina@uts.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tutor', 'Estadística', 9);

-- Estudiante de prueba (contraseña: est123)
INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol, carrera, semestre) VALUES
('Ana Sofía', 'López', 'ana@uts.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante', 'Ing. de Sistemas', 4);

-- Materias por tutor
INSERT INTO tutor_materias (tutor_id, materia_id) VALUES
(2, 4),(2, 5),(2, 6),(2, 10),  -- Carlos: Programación, Algoritmos, BD
(3, 1),(3, 2),(3, 3),(3, 9),   -- Laura: Cálculo, Álgebra, Estadística
(4, 7),(4, 8),                  -- Jorge: Física
(5, 9),(5, 3);                  -- Valentina: Estadística, Álgebra

-- Horarios
INSERT INTO horarios_tutor (tutor_id, dia_semana, hora_inicio, hora_fin, sala, modalidad) VALUES
(2, 'Lunes',    '14:00:00', '18:00:00', 'Sala B-204', 'ambas'),
(2, 'Miercoles','14:00:00', '18:00:00', 'Sala B-204', 'ambas'),
(3, 'Martes',   '08:00:00', '12:00:00', 'Sala A-101', 'ambas'),
(3, 'Jueves',   '08:00:00', '12:00:00', 'Sala A-101', 'ambas'),
(4, 'Miercoles','14:00:00', '17:00:00', 'Lab. Física', 'presencial'),
(4, 'Viernes',  '14:00:00', '17:00:00', 'Lab. Física', 'presencial'),
(5, 'Lunes',    '10:00:00', '14:00:00', 'Sala C-310', 'ambas'),
(5, 'Miercoles','10:00:00', '14:00:00', 'Sala C-310', 'ambas');
