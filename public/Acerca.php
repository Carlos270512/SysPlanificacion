
<link rel="stylesheet" href="assets/css/planificaciontyle.css">
<style>
  .about-card {
    max-width: 600px;
    margin: 40px auto 0 auto;
    border-radius: 18px;
    box-shadow: 0 6px 32px rgba(44,64,115,0.10), 0 1.5px 8px rgba(139,92,42,0.08);
    background: #fff;
    border-top: 6px solid #2c4073;
    border-bottom: 6px solid #2c4073;
    border-left: none;
    border-right: none;
    padding: 2.5rem 2.2rem 2rem 2.2rem;
    position: relative;
    text-align: center;
  }
  .about-title {
    color: #2c4073;
    font-weight: bold;
    letter-spacing: 0.5px;
    margin-bottom: 0.7rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
    font-size: 1.35em;
  }
  .about-title i {
    color: #8B5C2A;
    font-size: 1.6em;
  }
  .about-list {
    text-align: left;
    display: inline-block;
    margin: 0 auto 0 auto;
  }
  .about-list li {
    margin-bottom: 0.5rem;
    color: #2c4073;
    font-size: 1.08em;
    padding-left: 0.2em;
    position: relative;
  }
  .about-list li::before {
    content: '\2022';
    color: #8B5C2A;
    font-weight: bold;
    display: inline-block;
    width: 1em;
    margin-left: -1em;
  }
  .about-info {
    background: #f7f8fa;
    border-radius: 10px;
    padding: 1rem 1.2rem;
    margin-top: 1.5rem;
    color: #6B3F13;
    font-size: 1.05em;
    border-left: 5px solid #8B5C2A;
    display: inline-block;
    text-align: left;
  }
  .about-logo {
    display: flex;
    justify-content: center;
    margin-top: 2.2rem;
  }
  .about-logo img {
    height: 70px;
    border-radius: 12px;
    border: none;
    background: #fff;
    box-shadow: 0 2px 12px rgba(44,64,115,0.10);
  }
  @media (max-width: 700px) {
    .about-card { padding: 1.2rem 0.7rem; }
    .about-title { font-size: 1.2em; }
    .about-logo img { height: 48px; }
  }
</style>
<div class="about-card">
    <div class="about-title">
        <i class="fas fa-info-circle"></i>
        Acerca del Sistema de Planificación Académica
    </div>
    <p style="color:#222; font-size:1.08em;">
        Este sistema web permite a docentes, coordinadores y administradores gestionar y revisar planificaciones académicas, generar reportes, recibir notificaciones y mantener un control eficiente del proceso educativo en la institución.
    </p>
    <ul class="about-list">
        <li>Gestión de usuarios y roles (docentes, coordinadores, administradores)</li>
        <li>Creación, revisión y observación de planificaciones</li>
        <li>Notificaciones automáticas de observaciones pendientes</li>
        <li>Generación de reportes y exportación a PDF</li>
        <li>Importación y validación de datos desde archivos Excel</li>
    </ul>
    <div class="about-info">
        <strong>Versión:</strong> 1.0<br>
        <strong>Desarrollado por:</strong> Carlos Garces y Joseph Sanchez<br>
        <strong>Soporte:</strong> <a href="mailto:soporte@tusistema.com" style="color:#2c4073;">soporte@tusistema.com</a>
    </div>
    <div class="about-logo">
        <img src="assets/img/logotvn.png" alt="Logo TVN">
    </div>
</div>
