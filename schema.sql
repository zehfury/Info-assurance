-- schema.sql
-- SQL Injection Training Lab — local teaching use only.
-- Creates the database, the `users` table, and seeds ~30 fully fictional rows.

CREATE DATABASE IF NOT EXISTS sqli_lab;
USE sqli_lab;

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    fake_password_hash VARCHAR(64) NOT NULL
);

-- All names, emails, and hashes below are fictional and generated for this lab only.
INSERT INTO users (username, first_name, last_name, email, fake_password_hash) VALUES
('gwen.falcon',    'Gwen',    'Falcon',    'gwen.falcon@example.test',    'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4'),
('leo.marbles',    'Leo',     'Marbles',   'leo.marbles@example.test',    'b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5'),
('nadia.quorn',    'Nadia',   'Quorn',     'nadia.quorn@example.test',    'c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6'),
('otis.brambly',   'Otis',    'Brambly',   'otis.brambly@example.test',   'd4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1'),
('priya.stonewell', 'Priya',  'Stonewell', 'priya.stonewell@example.test','e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2'),
('felix.granger',  'Felix',   'Granger',   'felix.granger@example.test',  'f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3'),
('mira.oaksmith',  'Mira',    'Oaksmith',  'mira.oaksmith@example.test',  'a7b8c9d0e1f2a7b8c9d0e1f2a7b8c9d0'),
('dexter.holloway', 'Dexter', 'Holloway',  'dexter.holloway@example.test','b8c9d0e1f2a7b8c9d0e1f2a7b8c9d0e1'),
('juno.periwinkle', 'Juno',   'Periwinkle', 'juno.periwinkle@example.test','c9d0e1f2a7b8c9d0e1f2a7b8c9d0e1f2'),
('rowan.castellan', 'Rowan',  'Castellan', 'rowan.castellan@example.test','d0e1f2a7b8c9d0e1f2a7b8c9d0e1f2a7'),
('sable.thistlewood', 'Sable', 'Thistlewood', 'sable.thistlewood@example.test','e1f2a7b8c9d0e1f2a7b8c9d0e1f2a7b8'),
('theo.vantage',   'Theo',    'Vantage',   'theo.vantage@example.test',   'f2a7b8c9d0e1f2a7b8c9d0e1f2a7b8c9'),
('willa.fenmoor',  'Willa',   'Fenmoor',   'willa.fenmoor@example.test',  'a3b4c5d6e7f8a3b4c5d6e7f8a3b4c5d6'),
('zeke.hollister',  'Zeke',   'Hollister', 'zeke.hollister@example.test', 'b4c5d6e7f8a3b4c5d6e7f8a3b4c5d6e7'),
('ines.marlowe',   'Ines',    'Marlowe',   'ines.marlowe@example.test',   'c5d6e7f8a3b4c5d6e7f8a3b4c5d6e7f8'),
('oscar.brindlewood', 'Oscar', 'Brindlewood', 'oscar.brindlewood@example.test', 'd6e7f8a3b4c5d6e7f8a3b4c5d6e7f8a3'),
('hazel.quillfeather', 'Hazel', 'Quillfeather', 'hazel.quillfeather@example.test', 'e7f8a3b4c5d6e7f8a3b4c5d6e7f8a3b4'),
('milo.stanforth', 'Milo', 'Stanforth', 'milo.stanforth@example.test', 'f8a3b4c5d6e7f8a3b4c5d6e7f8a3b4c5'),
('freya.larkspur', 'Freya', 'Larkspur', 'freya.larkspur@example.test', 'a9b0c1d2e3f4a9b0c1d2e3f4a9b0c1d2'),
('corin.ashgrove', 'Corin', 'Ashgrove', 'corin.ashgrove@example.test', 'b0c1d2e3f4a9b0c1d2e3f4a9b0c1d2e3'),
('wren.fairweather', 'Wren', 'Fairweather', 'wren.fairweather@example.test', 'c1d2e3f4a9b0c1d2e3f4a9b0c1d2e3f4'),
('dashiell.thornbury', 'Dashiell', 'Thornbury', 'dashiell.thornbury@example.test', 'd2e3f4a9b0c1d2e3f4a9b0c1d2e3f4a9'),
('isolde.winterbourne', 'Isolde', 'Winterbourne', 'isolde.winterbourne@example.test', 'e3f4a9b0c1d2e3f4a9b0c1d2e3f4a9b0'),
('bram.nettlewood', 'Bram', 'Nettlewood', 'bram.nettlewood@example.test', 'f4a9b0c1d2e3f4a9b0c1d2e3f4a9b0c1'),
('talia.moorcroft', 'Talia', 'Moorcroft', 'talia.moorcroft@example.test', 'a5b6c7d8e9f0a5b6c7d8e9f0a5b6c7d8'),
('silas.penhallow', 'Silas', 'Penhallow', 'silas.penhallow@example.test', 'b6c7d8e9f0a5b6c7d8e9f0a5b6c7d8e9'),
('wynn.oakleigh', 'Wynn', 'Oakleigh', 'wynn.oakleigh@example.test', 'c7d8e9f0a5b6c7d8e9f0a5b6c7d8e9f0'),
('marisol.quillon', 'Marisol', 'Quillon', 'marisol.quillon@example.test', 'd8e9f0a5b6c7d8e9f0a5b6c7d8e9f0a5'),
('griffin.hartwell', 'Griffin', 'Hartwell', 'griffin.hartwell@example.test', 'e9f0a5b6c7d8e9f0a5b6c7d8e9f0a5b6'),
('elowen.frostgate', 'Elowen', 'Frostgate', 'elowen.frostgate@example.test', 'f0a5b6c7d8e9f0a5b6c7d8e9f0a5b6c7');
