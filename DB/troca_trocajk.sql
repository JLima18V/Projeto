CREATE DATABASE troca_trocaJK;

USE troca_trocaJK;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    nome_sobrenome varchar(255) not null,
    nome_usuario VARCHAR(50) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);




CREATE TABLE livros (
   id INT NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(255),
  autor VARCHAR(255),
  genero VARCHAR(100),
  estado VARCHAR(50),
  imagens varchar (255),
  id_usuario INT,
  data_publicacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
  );


