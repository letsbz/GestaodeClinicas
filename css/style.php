<?php
require_once '../config.php';
header('Content-Type: text/css');
?>
/* Estilos do Sistema de Gestão de Clínicas */

:root {
    --cor-primaria: <?= $cores['primaria'] ?>;
    --cor-secundaria: <?= $cores['secundaria'] ?>;
    --cor-accento-claro: <?= $cores['accento_claro'] ?>;
    --cor-accento-escuro: <?= $cores['accento_escuro'] ?>;
    --cor-branco: <?= $cores['branco'] ?>;
    --cor-cinza-claro: <?= $cores['cinza_claro'] ?>;
    --cor-cinza-medio: <?= $cores['cinza_medio'] ?>;
    --cor-cinza-escuro: <?= $cores['cinza_escuro'] ?>;
    --cor-azul-claro: <?= $cores['azul-claro'] ?>;
    --cor-erro: <?= $cores['erro'] ?>;
    --cor-sucesso: <?= $cores['sucesso'] ?>;
    --cor-aviso: <?= $cores['aviso'] ?>;
    --cor-erro-bg: #f8d7da;
    --cor-erro-hover: #a3120f;
    --cor-aviso-texto:rgb(168, 62, 0);
    --cor-aviso-bg:rgb(255, 171, 132);
    --cor-aviso-border:rgb(255, 122, 46);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--cor-cinza-claro);
    color: var(--cor-cinza-escuro);
    min-height: 100vh;
    font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Header e Navegação */
.header {
    background: var(--cor-primaria);
    color: var(--cor-branco);
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    border-bottom: 4px solid var(--cor-secundaria);
}

.header h1 {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 300;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    color: var(--cor-branco);
    letter-spacing: 1px;
}

/* Botões */
.btn {
    display: inline-block;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    text-align: center;
}

a.btn.btn-primary, button.btn.btn-primary, .btn.btn-primary {
    background: var(--cor-primaria) !important;
    color: var(--cor-branco) !important;
    border: none !important;
}
a.btn.btn-primary:hover, button.btn.btn-primary:hover, .btn.btn-primary:hover {
    background: var(--cor-secundaria) !important;
    color: var(--cor-branco) !important;
}

.btn-secondary {
    background: var(--cor-accento-claro);
    color: var(--cor-primaria);
    border: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-secondary:hover {
    background: var(--cor-accento-escuro);
    color: var(--cor-branco);
}

.btn-danger, .btn-ocultar {
    background: var(--cor-erro);
    color: var(--cor-branco);
    border: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-danger:hover, .btn-ocultar:hover {
    background: var(--cor-erro);
    filter: brightness(0.85);
    color: var(--cor-branco);
}

.btn-warning {
    background: var(--cor-aviso) !important;
    color: var(--cor-cinza-escuro) !important;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-warning:hover {
    background:rgb(194, 142, 0) !important;
    color: var(--cor-cinza-escuro);
}

.btn-success, .btn-reativar {
    background: var(--cor-sucesso);
    color: var(--cor-branco);
    border: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-success:hover, .btn-reativar:hover {
    background: var(--cor-primaria);
    color: var(--cor-branco);
}

.btn-info {
    background: var(--cor-secundaria);
    color: var(--cor-branco);
    border: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-info:hover {
    background: var(--cor-primaria);
    color: var(--cor-branco);
}

/* Cards */
.card, .form-container, .detalhes, .exames-section {
    background: var(--cor-branco) !important;
    color: var(--cor-cinza-escuro) !important;
    border: 1px solid var(--cor-cinza-medio);
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(25, 118, 210, 0.07);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card-header {
    border-bottom: 2px solid var(--cor-cinza-claro);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.card-header h2 {
    color: var(--cor-secundaria);
    font-size: 1.8rem;
    font-weight: 600;
}

/* Tabelas */
.table-container {
    background: var(--cor-branco);
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 2rem;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: var(--cor-branco);
}

th {
    background: var(--cor-secundaria);
    color: var(--cor-branco);
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

td {
    padding: 16px 12px;
    border-bottom: 1px solid var(--cor-cinza-medio);
    font-size: 14px;
    color: var(--cor-cinza-escuro);
    background: var(--cor-branco);
}

tr:hover {
    background: var(--cor-cinza-medio);
}

/* Formulários */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--cor-cinza-escuro);
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--cor-cinza-medio);
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: var(--cor-cinza-claro);
    color: var(--cor-cinza-escuro);
}

.form-control:focus {
    outline: none;
    border-color: var(--cor-azul-claro);
    box-shadow: 0 0 0 3px rgba(255, 255, 0, 0.1);
}

.form-control:invalid {
    border-color: var(--cor-erro);
}

/* Seções de Exames */
.exames-section {
    padding: 1.5rem;
    margin: 1.5rem 0;
    border-left: 4px solid var(--cor-azul-claro);
}

.exames-section h3 {
    color: var(--cor-azul-claro);
    margin-bottom: 1rem;
    font-size: 1.3rem;
    font-weight: 600;
}

.exames-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
    margin-top: 1rem;
}

.exame-item {
    background: var(--cor-branco);
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--cor-cinza-medio);
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.exame-item:hover {
    border-color: var(--cor-azul-claro);
    box-shadow: 0 2px 8px rgba(255, 255, 0, 0.1);
}

.exame-item input[type="checkbox"] {
    margin-right: 12px;
    transform: scale(1.2);
    accent-color: var(--cor-primaria);
}

.exame-item label {
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    flex: 1;
}

/* Mensagens */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border-left: 4px solid;
}

.alert-error {
    background: #ffebee;
    color: var(--cor-erro);
    border-left: 5px solid var(--cor-erro);
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.alert-success {
    background: #e8f5e9;
    color: var(--cor-sucesso);
    border-left: 5px solid var(--cor-sucesso);
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.alert-warning, .alert.alert-warning {
    background: #fff8e1;
    color: var(--cor-aviso) !important;
    border-left: 5px solid var(--cor-aviso) !important;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

/* Ações */
.actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.actions .btn {
    flex: 1;
    min-width: 120px;
}

/* Detalhes */
.detalhes dt {
    font-weight: 600;
    color: var(--cor-primaria);
    margin-top: 1rem;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detalhes dd {
    margin-left: 0;
    margin-bottom: 1rem;
    padding: 8px 0;
    border-bottom: 1px solid var(--cor-cinza-claro);
    font-size: 16px;
}

/* Lista de Exames */
.exames-list ul {
    list-style: none;
    padding: 0;
}

.exames-list li {
    background: var(--cor-cinza-claro);
    padding: 12px 16px;
    margin-bottom: 8px;
    border-radius: 6px;
    border-left: 3px solid var(--cor-primaria);
    font-size: 14px;
}

.exames-list li small {
    display: block;
    margin-top: 4px;
    color: var(--cor-cinza-escuro);
    font-size: 12px;
}

/* Responsividade */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    .header h1 {
        font-size: 2rem;
    }
    
    .exames-grid {
        grid-template-columns: 1fr;
    }
    
    .actions {
        flex-direction: column;
    }
    
    .actions .btn {
        width: 100%;
    }
    
    .form-container,
    .detalhes {
        margin: 10px;
        padding: 1rem;
    }
}

/* Animações */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card, .form-container, .detalhes {
    animation: fadeIn 0.5s ease-out;
}

/* Estados de loading */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Melhorias de acessibilidade */
.btn:focus,
.form-control:focus {
    outline: 2px solid var(--cor-azul-claro);
    outline-offset: 2px;
}

/* Scrollbar personalizada */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: var(--cor-cinza-claro);
}

::-webkit-scrollbar-thumb {
    background: var(--cor-accento-claro);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--cor-primaria);
}

input, select, .form-control, .bootstrap-select .dropdown-toggle {
    background: var(--cor-branco) !important;
    color: var(--cor-cinza-escuro) !important;
    border: 1px solid var(--cor-cinza-medio) !important;
    border-radius: 6px !important;
    transition: border 0.2s;
}

input:focus, select:focus, .form-control:focus, .bootstrap-select .dropdown-toggle:focus {
    border-color: var(--cor-primaria) !important;
    outline: 2px solid var(--cor-accento-claro) !important;
}

.bootstrap-select .dropdown-menu {
    background: var(--cor-branco);
    color: var(--cor-cinza-escuro);
}

.bootstrap-select .dropdown-item.active, .bootstrap-select .dropdown-item:active {
    background: var(--cor-primaria);
    color: var(--cor-branco);
}

.bootstrap-select .dropdown-item:hover {
    background: var(--cor-accento-claro);
    color: var(--cor-primaria);
}

/* Títulos e detalhes */
h1, h2, h3, h4, h5, h6, dt {
    color: var(--cor-secundaria);
    font-weight: 700;
}

.detalhes dt {
    font-weight: 600;
}

.detalhes dd {
    margin-bottom: 1rem;
}

a, .link {
    color: var(--cor-primaria);
    text-decoration: underline;
    transition: color 0.2s;
}

a:hover, .link:hover {
    color: var(--cor-secundaria);
}

.clinicas-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-top: 2rem;
}

.clinica-card {
    background: var(--cor-branco);
    border: 1px solid var(--cor-cinza-medio);
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(25, 118, 210, 0.07);
    padding: 1.5rem 1.2rem 1.2rem 1.2rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    min-height: 210px;
    position: relative;
    transition: box-shadow 0.2s;
}
.clinica-card:hover {
    box-shadow: 0 4px 24px rgba(25, 118, 210, 0.13);
    border-color: var(--cor-primaria);
}
.card-actions {
    display: flex;
    gap: 8px;
    position: absolute;
    top: 12px;
    left: 12px;
}
.clinica-info {
    margin-top: 40px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.clinica-nome {
    font-size: 1.1rem;
    color: var(--cor-secundaria);
    font-weight: 700;
}
.clinica-cnpj, .clinica-local, .clinica-tel {
    font-size: 0.97rem;
    color: var(--cor-cinza-escuro);
}
@media (max-width: 1100px) {
    .clinicas-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 700px) {
    .clinicas-grid {
        grid-template-columns: 1fr;
    }
    .clinica-card {
        min-height: 180px;
        padding: 1rem;
    }
    .card-actions {
        top: 8px;
        left: 8px;
    }
    .clinica-info {
        margin-top: 36px;
    }
}

.exame-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    justify-content: center;
}

.btn-ocultar i, .btn-reativar i {
    font-size: 1.2em;
    vertical-align: middle;
    margin: 0;
    color: var(--cor-branco) !important;
    display: inline-block;
}

.btn-ocultar, .btn-reativar {
    padding: 6px 10px !important;
    min-width: 32px;
    min-height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* =====================
   Estilos para Exames (Clínica)
   ===================== */
.exames-duas-colunas {
  display: flex;
  gap: 32px;
  margin-bottom: 2rem;
}
.exames-duas-colunas > .exames-section {
  flex: 1 1 0;
  min-width: 0;
}
@media (max-width: 900px) {
  .exames-duas-colunas {
    flex-direction: column;
    gap: 0;
  }
}

.selectpicker.form-control {
  min-height: 38px;
  height: auto !important;
  padding-top: 6px;
  padding-bottom: 6px;
  margin-bottom: 0;
}
.form-group .bootstrap-select {
  width: 100% !important;
  margin-bottom: 0;
}
.form-group {
  margin-bottom: 0.5rem;
  position: relative;
}
.form-inline-exames {
  display: flex;
  gap: 8px;
  align-items: flex-end;
}
.form-inline-exames .form-group {
  flex: 1 1 0;
  margin-bottom: 0;
}
.input-group .bootstrap-select.form-control { flex: 1 1 auto; width: 1%; }
.input-group .bootstrap-select > .dropdown-toggle { border-top-right-radius: 0; border-bottom-right-radius: 0; }
.input-group .input-group-append .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }
.detalhes .row > div > dl {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 0.2rem 1rem;
}
.detalhes .row > div > dl > dt {
  grid-column: 1;
  font-weight: 600;
  color: var(--cor-primaria, #327217);
  margin-bottom: 0;
}
.detalhes .row > div > dl > dd {
  grid-column: 2;
  margin-bottom: 0.5rem;
  margin-left: 0;
}

/* =====================
   Estilos para Login
   ===================== */
body.login-page { background: var(--cor-cinza-claro); }
.login-container {
    max-width: 400px;
    margin: 60px auto;
    background: var(--cor-branco);
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(25, 118, 210, 0.07);
    padding: 2.5rem 2rem 2rem 2rem;
}
.login-header {
    text-align: center;
    margin-bottom: 2rem;
}
.login-header h2 {
    color: var(--cor-primaria);
    font-weight: 700;
    font-size: 2rem;
}
.login-container .form-group label {
    color: var(--cor-cinza-escuro);
    font-weight: 600;
}
.btn-login {
    width: 100%;
    background: var(--cor-primaria) !important;
    color: var(--cor-branco) !important;
    font-weight: 600;
    border-radius: 6px;
    padding: 12px;
    font-size: 1.1rem;
    margin-top: 1rem;
    transition: background 0.2s;
    border: none;
}
.btn-login:hover, .btn-login:focus, .btn-login:active {
    background: var(--cor-secundaria) !important;
    color: var(--cor-branco) !important;
    border: none;
    outline: none;
}
.login-erro {
    background: #ffebee;
    color: var(--cor-erro);
    border-left: 5px solid var(--cor-erro);
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    text-align: center;
}
.popup-inativo {
    background: var(--cor-aviso);
    color: var(--cor-aviso-texto, var(--cor-cinza-escuro));
    border-left: 5px solid #ffe082;
    border-radius: 6px;
    padding: 1.2rem 1rem;
    margin-bottom: 1.5rem;
    text-align: center;
    font-size: 1.1rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
}

.modal-sucesso-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.25);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-sucesso-content {
    width: 100%;
    max-width: 400px;
    min-width: 280px;
    background: var(--cor-branco) !important;
    border-color: var(--cor-accento_claro) !important;
    color: var(--cor-accento_claro) !important;
    border-radius: 12px;
    padding: 2rem 2.5rem;
    text-align: center;
    margin: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.btn-header {
    border-radius: 6px !important;
    padding: 12px 24px !important;
    min-width: 120px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
}

.btn-header.btn-info {
    background: var(--cor-secundaria) !important;
    color: var(--cor-branco) !important;
    border: none !important;
}
.btn-header.btn-danger {
    background: var(--cor-erro) !important;
    color: var(--cor-branco) !important;
    border: none !important;
}


