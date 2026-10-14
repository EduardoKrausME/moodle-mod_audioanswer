<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * audioanswer.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['audio'] = 'Áudio';
$string['audioanswer:addinstance'] = 'Adicionar uma nova atividade Resposta por áudio';
$string['audioanswer:submit'] = 'Enviar uma resposta por áudio';
$string['audioanswer:viewresponses'] = 'Ver todas as respostas por áudio';
$string['audioanswername'] = 'Nome da atividade';
$string['backtoactivity'] = 'Voltar para a atividade';
$string['completiononsubmit'] = 'O aluno deve enviar uma resposta por áudio';
$string['currentresponse'] = 'Sua resposta atual';
$string['duration'] = 'Duração';
$string['enabletranscription'] = 'Transcrever as respostas por áudio com o AI Bridge';
$string['enabletranscription_help'] = 'Quando ativado, os novos áudios são enviados ao provedor de IA configurado para a finalidade audioanswer-transcription. O áudio sai do servidor Moodle e pode gerar custos. A transcrição fica visível para o aluno e para professores autorizados a visualizar respostas. É necessário configurar o local_ai_bridge, a finalidade no tenant e uma rota para um modelo de transcrição. Gravações anteriores podem ser transcritas pelo relatório do professor.';
$string['eventanswersubmitted'] = 'Resposta por áudio enviada';
$string['eventcoursemoduleviewed'] = 'Atividade Resposta por áudio visualizada';
$string['invalidaudiotype'] = 'O arquivo enviado não é uma gravação de áudio suportada.';
$string['invalidduration'] = 'A atividade possui uma configuração inválida de duração.';
$string['invalidfilesize'] = 'O arquivo de áudio enviado possui tamanho inválido.';
$string['lastsubmitted'] = 'Último envio';
$string['maxduration'] = 'Tempo máximo de gravação';
$string['maxduration_help'] = 'Escolha o limite de gravação disponível no navegador: 30, 60 ou 120 segundos.';
$string['microphoneerror'] = 'Não foi possível acessar o microfone.';
$string['microphonepermission'] = 'Aguardando permissão para usar o microfone…';
$string['missingaudio'] = 'Nenhum arquivo de áudio foi recebido.';
$string['modulename'] = 'Resposta por áudio';
$string['modulenameplural'] = 'Respostas por áudio';
$string['noinstances'] = 'Não há atividades de resposta por áudio neste curso.';
$string['permissiondenied'] = 'O navegador não recebeu permissão para usar o microfone.';
$string['pluginadministration'] = 'Administração da Resposta por áudio';
$string['pluginname'] = 'Resposta por áudio';
$string['preview'] = 'Ouvir antes de enviar';
$string['privacy:metadata:ai_provider'] = 'Um provedor externo de IA recebe o áudio quando a transcrição está ativada na atividade.';
$string['privacy:metadata:ai_provider:audio'] = 'A gravação privada é transmitida ao provedor de IA configurado para realizar a transcrição.';
$string['privacy:metadata:audioanswer_response'] = 'Armazena os metadados da resposta por áudio de cada aluno.';
$string['privacy:metadata:audioanswer_response:audioanswerid'] = 'A atividade de resposta por áudio à qual a resposta pertence.';
$string['privacy:metadata:audioanswer_response:duration'] = 'A duração declarada da gravação em segundos.';
$string['privacy:metadata:audioanswer_response:filename'] = 'O nome do arquivo de áudio armazenado.';
$string['privacy:metadata:audioanswer_response:mimetype'] = 'O tipo MIME da gravação armazenada.';
$string['privacy:metadata:audioanswer_response:timecreated'] = 'O momento em que a resposta foi criada pela primeira vez.';
$string['privacy:metadata:audioanswer_response:timemodified'] = 'O momento em que a resposta foi atualizada pela última vez.';
$string['privacy:metadata:audioanswer_response:transcription'] = 'Transcrição em texto simples gerada por IA para a gravação enviada.';
$string['privacy:metadata:audioanswer_response:transcriptionstatus'] = 'Estado do processamento da transcrição por IA.';
$string['privacy:metadata:audioanswer_response:userid'] = 'O usuário que enviou a resposta.';
$string['privacy:metadata:core_files'] = 'As gravações de áudio são armazenadas pela File API do Moodle.';
$string['question'] = 'Pergunta';
$string['question_help'] = 'Digite a pergunta ou orientação que o aluno deverá responder gravando um áudio curto.';
$string['readytorecord'] = 'Pronto para gravar.';
$string['readytosave'] = 'Gravação concluída. Ouça antes de salvar.';
$string['record'] = 'Gravar áudio';
$string['recording'] = 'Gravando…';
$string['recordingtoolong'] = 'A gravação ultrapassa o tempo permitido.';
$string['recordingunsupported'] = 'Este navegador não suporta gravação de áudio diretamente na página.';
$string['replacehint'] = 'Se você gravar e salvar novamente, esta resposta será substituída.';
$string['requesttranscription'] = 'Transcrever áudio';
$string['responses'] = 'Respostas por áudio';
$string['saveanswer'] = 'Salvar resposta';
$string['saved'] = 'Resposta por áudio salva.';
$string['saveerror'] = 'Não foi possível salvar a resposta por áudio. Tente novamente.';
$string['saving'] = 'Salvando o áudio…';
$string['seconds'] = '{$a} segundos';
$string['stoprecording'] = 'Parar';
$string['student'] = 'Aluno';
$string['submitted'] = 'Enviado';
$string['timelimit'] = 'Tempo máximo de gravação: {$a} segundos';
$string['transcription'] = 'Transcrição';
$string['transcriptiondisabled'] = 'A transcrição não está ativada nesta atividade.';
$string['transcriptionempty'] = 'Nenhuma fala foi identificada nesta gravação.';
$string['transcriptionfailed'] = 'Não foi possível transcrever o áudio. Um professor pode solicitar novamente pelo relatório de respostas.';
$string['transcriptionpending'] = 'A transcrição está sendo processada. Atualize a página posteriormente.';
$string['transcriptionqueued'] = 'A transcrição do áudio foi colocada na fila.';
$string['transcriptionunavailable'] = 'A transcrição do AI Bridge não está instalada ou não é compatível.';
$string['uploaderror'] = 'Falha no envio do áudio.';
$string['viewresponses'] = 'Ver respostas';
