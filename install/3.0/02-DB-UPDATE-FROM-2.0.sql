
-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 21/07/2020 às 17:34
-- Versão do servidor: 8.0.18
-- Versão do PHP: 7.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Banco de dados: `ocomon_2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `worktime_profiles`
--

CREATE TABLE `worktime_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_default` tinyint(1) DEFAULT NULL,
  `week_ini_time_hour` varchar(2) NOT NULL,
  `week_ini_time_minute` varchar(2) NOT NULL,
  `week_end_time_hour` varchar(2) NOT NULL,
  `week_end_time_minute` varchar(2) NOT NULL,
  `week_day_full_worktime` int(5) NOT NULL,
  `sat_ini_time_hour` varchar(2) NOT NULL,
  `sat_ini_time_minute` varchar(2) NOT NULL,
  `sat_end_time_hour` varchar(2) NOT NULL,
  `sat_end_time_minute` varchar(2) NOT NULL,
  `sat_day_full_worktime` int(5) NOT NULL,
  `sun_ini_time_hour` varchar(2) NOT NULL,
  `sun_ini_time_minute` varchar(2) NOT NULL,
  `sun_end_time_hour` varchar(2) NOT NULL,
  `sun_end_time_minute` varchar(2) NOT NULL,
  `sun_day_full_worktime` int(5) NOT NULL,
  `off_ini_time_hour` varchar(2) NOT NULL,
  `off_ini_time_minute` varchar(2) NOT NULL,
  `off_end_time_hour` varchar(2) NOT NULL,
  `off_end_time_minute` varchar(2) NOT NULL,
  `off_day_full_worktime` int(5) NOT NULL,
  `247` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cargas horárias para controle de parada de relógio e SLAs';

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `worktime_profiles`
--
ALTER TABLE `worktime_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `is_default` (`is_default`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `worktime_profiles`
--
ALTER TABLE `worktime_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  
INSERT INTO `worktime_profiles` (`id`, `name`, `is_default`, `week_ini_time_hour`, `week_ini_time_minute`, `week_end_time_hour`, `week_end_time_minute`, `week_day_full_worktime`, `sat_ini_time_hour`, `sat_ini_time_minute`, `sat_end_time_hour`, `sat_end_time_minute`, `sat_day_full_worktime`, `sun_ini_time_hour`, `sun_ini_time_minute`, `sun_end_time_hour`, `sun_end_time_minute`, `sun_day_full_worktime`, `off_ini_time_hour`, `off_ini_time_minute`, `off_end_time_hour`, `off_end_time_minute`, `off_day_full_worktime`, `247`) VALUES ('1', 'DEFAULT', '1', '00', '00', '23', '59', '1440', '00', '00', '23', '59', '1440', '00', '00', '23', '59', '1440', '00', '00', '23', '59', '1440', '1');

  
  
ALTER TABLE `sistemas` ADD `sis_wt_profile` INT(2) NOT NULL DEFAULT '1' COMMENT 'id do perfil de jornada de trabalho' AFTER `sis_screen`, ADD INDEX (`sis_wt_profile`); 
  

ALTER TABLE `config` ADD `conf_wt_areas` ENUM('1','2') NOT NULL DEFAULT '2' COMMENT '1: área origem, 2: área destino' AFTER `conf_qtd_max_anexos`, ADD INDEX (`conf_wt_areas`); 
  
  
ALTER TABLE `status` ADD `stat_time_freeze` TINYINT(1) NOT NULL DEFAULT '0' AFTER `stat_painel`, ADD INDEX (`stat_time_freeze`); 

UPDATE `status` SET `stat_time_freeze` = 1 WHERE stat_id IN (4,12,16);
  
  

CREATE TABLE `tickets_stages` ( `id` BIGINT NOT NULL AUTO_INCREMENT , `ticket` INT NOT NULL , `date_start` DATETIME NOT NULL , `date_stop` DATETIME NOT NULL , `status_id` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`ticket`), INDEX (`status_id`)) ENGINE = InnoDB COMMENT = 'Intervalos de tempo para cada status do chamado'; 
  
ALTER TABLE `tickets_stages` CHANGE `date_stop` `date_stop` DATETIME NULL DEFAULT NULL; 

ALTER TABLE `ocorrencias` ADD `oco_scheduled_to` DATETIME NULL DEFAULT NULL AFTER `oco_scheduled`; 
  
  
  
CREATE TABLE `ocorrencias_log` ( `log_id` INT(11) NOT NULL AUTO_INCREMENT , `log_numero` INT(11) NOT NULL , `log_quem` INT(5) NOT NULL , `log_data` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `log_prioridade` INT(2) NULL DEFAULT NULL , `log_area` INT(4) NULL DEFAULT NULL , `log_problema` INT(4) NULL DEFAULT NULL , `log_unidade` INT(4) NULL DEFAULT NULL , `log_etiqueta` INT(11) NULL DEFAULT NULL , `log_contato` VARCHAR(255) NULL DEFAULT NULL , `log_telefone` VARCHAR(255) NULL DEFAULT NULL , `log_departamento` INT(4) NULL DEFAULT NULL , `log_responsavel` INT(5) NULL DEFAULT NULL , `log_data_agendamento` DATETIME NULL DEFAULT NULL , `log_status` INT(4) NULL DEFAULT NULL , `log_tipo_edicao` INT(2) NULL DEFAULT NULL , PRIMARY KEY (`log_id`), INDEX (`log_numero`)) ENGINE = InnoDB COMMENT = 'Log de alteracoes nas informacoes dos chamados';

ALTER TABLE `ocorrencias_log` ADD `log_descricao` TEXT NULL DEFAULT NULL AFTER `log_data`;   
  
  
  
  
  
ALTER TABLE `utmp_usuarios` CHANGE `utmp_nome` `utmp_nome` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 
ALTER TABLE `utmp_usuarios` CHANGE `utmp_email` `utmp_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `utmp_passwd` `utmp_passwd` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `utmp_rand` `utmp_rand` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `utmp_usuarios` ADD `utmp_phone` VARCHAR(255) NULL AFTER `utmp_email`; 
ALTER TABLE `utmp_usuarios` ADD `utmp_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `utmp_rand`; 


ALTER TABLE `usuarios` ADD `last_logon` DATETIME NULL AFTER `user_admin`; 
  
  
  
ALTER TABLE `global_tickets` CHANGE `gt_id` `gt_id` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `imagens` CHANGE `img_tipo` `img_tipo` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 




ALTER TABLE `prob_tipo_1` CHANGE `probt1_desc` `probt1_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `prob_tipo_2` CHANGE `probt2_desc` `probt2_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `prob_tipo_3` CHANGE `probt3_desc` `probt3_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 





ALTER TABLE `ocorrencias` CHANGE `equipamento` `equipamento` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `ocorrencias_log` CHANGE `log_etiqueta` `log_etiqueta` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `imagens` CHANGE `img_inv` `img_inv` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `equipamentos` CHANGE `comp_inv` `comp_inv` VARCHAR(255) NOT NULL; 



ALTER TABLE `estoque` CHANGE `estoq_tag_inv` `estoq_tag_inv` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `historico` CHANGE `hist_inv` `hist_inv` VARCHAR(255) NOT NULL DEFAULT '0'; 
ALTER TABLE `hist_pieces` CHANGE `hp_comp_inv` `hp_comp_inv` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `hw_alter` CHANGE `hwa_inv` `hwa_inv` VARCHAR(255) NOT NULL; 
ALTER TABLE `hw_sw` CHANGE `hws_hw_cod` `hws_hw_cod` VARCHAR(255) NOT NULL DEFAULT '0'; 
ALTER TABLE `moldes` CHANGE `mold_inv` `mold_inv` VARCHAR(255) NULL DEFAULT NULL; 






INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'agendamento-para-area', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Chamado Agendado', 'Caro operador\r\n\r\nO chamado número %numero% foi editado e marcado como agendado para a seguinte data:\r\nDia: %dia_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO dia e horário marcados indicam quando o chamado entrará novamente na fila de atendimento.\r\n\r\nAtte. Equipe de Suporte', 'Caro operador\r\n\r\nO chamado número %numero% foi editado e marcado como agendado para a seguinte data:\r\nDia: %data_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO dia e horário marcados indicam quando o chamado entrará novamente na fila de atendimento.\r\n\r\nAtte. Equipe de Suporte'); 

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'agendamento-para-usuario', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Chamado Agendado', 'Caro %usuario%,\r\n\r\nSeu chamado foi marcado como agendado para a seguinte data e horário:\r\nDia: %dia_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO agendamento do chamado indica que ele entrará novamente na fila de atendimento a partir da data informada.\r\n\r\nAtte.\r\nEquipe de Suporte.', 'Caro %usuario%,\r\n\r\nSeu chamado foi marcado como agendado para a seguinte data e horário:\r\nDia: %dia_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO agendamento do chamado indica que ele entrará novamente na fila de atendimento a partir da data informada.\r\n\r\nAtte.\r\nEquipe de Suporte.'); 



CREATE TABLE `environment_vars` ( `id` INT NOT NULL AUTO_INCREMENT , `vars` TEXT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'Variáveis de ambiente para e-mails de notificações'; 

INSERT INTO `environment_vars` (`id`, `vars`) VALUES (NULL, '<p><strong>N&uacute;mero do chamado:</strong> %numero%<br />\r\n<strong>Contato:</strong> %usuario%<br />\r\n<strong>Contato: </strong>%contato%<br />\r\n<strong>E-mail do Contato: </strong>%contato_email%<br />\r\n<strong>Descri&ccedil;&atilde;o do chamado:</strong> %descricao%<br />\r\n<strong>Departamento do chamado:</strong> %departamento%<br />\r\n<strong>Telefone:</strong> %telefone%<br />\r\n<strong>Site para acesso ao OcoMon:</strong> %site%<br />\r\n<strong>&Aacute;rea de atendimento:</strong> %area%<br />\r\n<strong>Operador do chamado:</strong> %operador%<br />\r\n<strong>Operador do chamado:</strong> %editor%<br />\r\n<strong>Quem abriu o chamado:</strong> %aberto_por%<br />\r\n<strong>Tipo de problema:</strong> %problema%<br />\r\n<strong>Vers&atilde;o do OcoMon:</strong> %versao%<br />\r\n<strong>Url global para acesso ao chamado:</strong> %url%<br />\r\n<strong>Url global para acesso ao chamado:</strong> %linkglobal%<br />\r\n<strong>Unidade: </strong>%unidade%<br />\r\n<strong>Etiqueta:</strong> %etiqueta%<br />\r\n<strong>Unidade e Etiqueta:</strong> %patrimonio%<br />\r\n<strong>Data de abertura do chamado:</strong> %data_abertura%<br />\r\n<strong>Status do chamado:</strong> %status%<br />\r\n<strong>Data de agendamento do chamado:</strong> %data_agendamento%<br />\r\n<strong>Data de encerramento do chamado:</strong> %data_fechamento%<br />\r\n<strong>Apenas o dia do agendamento:</strong> %dia_agendamento%<br />\r\n<strong>Apenas a hora do agendamento:</strong> %hora_agendamento%<br />\r\n<strong>Descri&ccedil;&atilde;o t&eacute;cnica (para chamados encerrados):</strong> %descricao_tecnica%<br />\r\n<strong>Solu&ccedil;&atilde;o t&eacute;cnica (para chamados encerrados):</strong> %solucao%<br />\r\n<strong>&Uacute;ltimo assentamento do chamado:</strong> %assentamento%</p>');



ALTER TABLE `avisos` ADD `expire_date` DATETIME NULL DEFAULT NULL AFTER `origembkp`, ADD `is_active` TINYINT NULL DEFAULT NULL AFTER `expire_date`, ADD INDEX (`expire_date`), ADD INDEX (`is_active`); 

ALTER TABLE `avisos` ADD `title` VARCHAR(30) NULL DEFAULT NULL AFTER `aviso_id`; 
ALTER TABLE `avisos` CHANGE `area` `area` VARCHAR(255) NULL DEFAULT NULL; 



CREATE TABLE `user_notices` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `notice_id` INT NOT NULL , `last_shown` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`notice_id`), INDEX (`last_shown`)) ENGINE = InnoDB COMMENT = 'Avisos do Mural já exibidos para o usuário'; 


ALTER TABLE `config` ADD `conf_sla_tolerance` INT(2) NOT NULL DEFAULT '20' COMMENT 'Percentual de Tolerância de SLA - entre o verde e o vermelho' AFTER `conf_wt_areas`; 



ALTER TABLE `ocorrencias` ADD `contato_email` VARCHAR(255) NULL DEFAULT NULL AFTER `contato`, ADD INDEX (`contato_email`); 

ALTER TABLE `configusercall` ADD `conf_scr_contact_email` INT(1) NOT NULL DEFAULT '0' AFTER `conf_scr_prior`; 


ALTER TABLE `ocorrencias_log` ADD `log_contato_email` VARCHAR(255) NULL DEFAULT NULL AFTER `log_contato`; 
  
  
INSERT INTO `avisos` (`aviso_id`, `title`, `avisos`, `data`, `origem`, `status`, `area`, `origembkp`, `expire_date`, `is_active`) VALUES (NULL, 'Bem vindo!', '<p>Seja muito bem vindo ao OcoMon 3.0, o melhor OcoMon de todos os tempos!</p><hr />
<p>N&atilde;o esque&ccedil;a de ajustar as configura&ccedil;&otilde;es do sistema de acordo com suas necessidades.</p><hr />
<p>Acesse o <a href="https://www.youtube.com/channel/UCFikgr9Xk2bE__snw1_RYtQ" target="_blank">canal no Youtube</a> para dicas e informa&ccedil;&otilde;es diversas a respeito do sistema.</p>', CURRENT_TIME(), '1', 'success', '1', NULL, CURRENT_TIME(), '1'); 
  
  
ALTER TABLE `equipxpieces` CHANGE `eqp_equip_inv` `eqp_equip_inv` VARCHAR(255) NOT NULL;   
  

  
  
COMMIT;

