const base = (typeof API_BASE !== 'undefined' && API_BASE) ? API_BASE : '/back/public/api';


const API = {
  listChamados:  () => $.ajax({ url: `${base}/chamados`, dataType: 'json' }),
  getChamado:    (id) => $.ajax({ url: `${base}/chamados/${id}`, dataType: 'json' }),
  createChamado: (data) => $.ajax({
    url: `${base}/chamados`,
    method: 'POST',
    data: JSON.stringify(data),
    contentType: 'application/json',
    dataType: 'json'
  }),
  updateChamado: (id, data) => $.ajax({
    url: `${base}/chamados/${id}`,
    method: 'PUT',
    data: JSON.stringify(data),
    contentType: 'application/json'
  }),
  deleteChamado: (id) => $.ajax({ url: `${base}/chamados/${id}`, method: 'DELETE' }),



  listTarefas:   (chamadoId) => $.ajax({ url: `${base}/chamados/${chamadoId}/tarefas`, dataType: 'json' }),
  createTarefa:  (data) => $.ajax({
    url: `${base}/tarefas`,
    method: 'POST',
    data: JSON.stringify(data),
    contentType: 'application/json',
    dataType: 'json'
  }),
  updateTarefa:  (id, data) => $.ajax({
    url: `${base}/tarefas/${id}`,
    method: 'PUT',
    data: JSON.stringify(data),
    contentType: 'application/json'
  }),
  deleteTarefa:  (id) => $.ajax({ url: `${base}/tarefas/${id}`, method: 'DELETE' }),
};



function errText(xhr){
  return (xhr && xhr.responseJSON && xhr.responseJSON.error)
    ? xhr.responseJSON.error
    : (xhr && xhr.status ? xhr.status : 'erro');
}
function fmtDate(iso){
  if (!iso) return '';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return iso;
  return d.toLocaleString('pt-BR');
}




function rowChamado(c){
  const cur = String(c.status || '').toLowerCase();
  return `
<tr data-id="${c.id}">
  <td>${c.id}</td>
  <td class="tit">${c.titulo}</td>
  <td>
    <select class="status-chamado form-select form-select-sm">
      ${['Aberto','Em Andamento','Finalizado'].map(label => {
        const val = label.toLowerCase();
        return `<option value="${label}" ${cur === val ? 'selected' : ''}>${label}</option>`;
      }).join('')}
    </select>
  </td>
  <td>${fmtDate(c.created_at)}</td>
  <td class="acoes">
    <button class="btn btn-sm btn-dark ver">Ver</button>
    <button class="btn btn-sm btn-dark excluir">Excluir</button>
  </td>
</tr>`;
}

function rowTarefa(t){
  const cur = String(t.status || '').toLowerCase();
  return `
<tr data-id="${t.id}">
  <td>${t.id}</td>
  <td contenteditable="true" class="desc">${t.descricao}</td>
  <td contenteditable="true" class="resp">${t.responsavel}</td>
  <td>
    <select class="status-tarefa form-select form-select-sm">
      ${['Pendente','Fazendo','Concluida'].map(label => {
        const val = label.toLowerCase();
        return `<option value="${label}" ${cur === val ? 'selected' : ''}>${label}</option>`;
      }).join('')}
    </select>
  </td>
  <td>${fmtDate(t.created_at)}</td>
  <td class="acoes">
    <button class="btn btn-sm btn-dark salvar">Salvar</button>
    <button class="btn btn-sm btn-dark excluir">Excluir</button>
  </td>
</tr>`;
}



function carregarChamados(){
  API.listChamados()
    .done(items => {
      const list = Array.isArray(items) ? items : [];
      const $tb = $('#tblChamados tbody').empty();
      list.forEach(c => $tb.append(rowChamado(c)));
      $('#semResultados').toggleClass('d-none', list.length !== 0);
    })
    .fail(xhr => alert(`Erro ao listar chamados: ${errText(xhr)}`));
}

function carregarTarefas(chamadoId){
  API.listTarefas(chamadoId)
    .done(items => {
      const list = Array.isArray(items) ? items : [];
      const $tb = $('#tblTarefas tbody').empty();
      list.forEach(t => $tb.append(rowTarefa(t)));
      $('#semTarefas').toggleClass('d-none', list.length !== 0);
    })
    .fail(xhr => alert(`Erro ao listar tarefas: ${errText(xhr)}`));
}

function abrirChamado(id){
  API.getChamado(id)
    .done(c => {
      $('#painelChamado').removeClass('d-none');
      $('#tituloChamado').text(`Chamado #${c.id} – ${c.titulo}`);
      $('#dadosChamado').html(`
        <div class="row g-2">
          <div class="col-12"><b>Status:</b> ${c.status}</div>
          <div class="col-12"><b>Descrição:</b><br/>${(c.descricao||'').replace(/\n/g,'<br>')}</div>
        </div>`);
      $('#formNovaTarefa [name="chamado_id"]').val(c.id);
      carregarTarefas(c.id);
    })
    .fail(xhr => alert(`Erro ao carregar chamado: ${errText(xhr)}`));
}





// criar chamado
$(document).on('submit', '#formNovoChamado', function(e){
  e.preventDefault();
  const data = Object.fromEntries(new FormData(this).entries());
  API.createChamado(data)
    .done(() => { this.reset(); carregarChamados(); })
    .fail(xhr => alert(`Erro ao criar: ${errText(xhr)}`));
});

// abrir painel do chamado
$(document).on('click', '#tblChamados .ver', function(){
  const id = $(this).closest('tr').data('id');
  abrirChamado(id);
});

// excluir chamado
$(document).on('click', '#tblChamados .excluir', function(){
  const id = $(this).closest('tr').data('id');
  if (!confirm(`Excluir chamado #${id}?`)) return;
  API.deleteChamado(id)
    .done(() => { carregarChamados(); $('#painelChamado').addClass('d-none'); })
    .fail(xhr => alert(`Erro ao excluir: ${errText(xhr)}`));
});

// alterar status do chamado 
$(document).on('change', '#tblChamados .status-chamado', function(){
  const $tr = $(this).closest('tr');
  const id = $tr.data('id');
  const status = $(this).val();
  API.updateChamado(id, { status })
    .fail(xhr => alert(`Erro ao atualizar: ${errText(xhr)}`));
});

// fechar painel
$('#btnFecharPainel').on('click', () => $('#painelChamado').addClass('d-none'));

// criar tarefa
$(document).on('submit', '#formNovaTarefa', function(e){
  e.preventDefault();
  const data = Object.fromEntries(new FormData(this).entries());
  data.status = data.status || 'Aberto';
  API.createTarefa(data)
    .done(() => { this.reset(); carregarTarefas(data.chamado_id); })
    .fail(xhr => alert(`Erro ao criar tarefa: ${errText(xhr)}`));
});

// excluir tarefa
$(document).on('click', '#tblTarefas .excluir', function(){
  const $tr = $(this).closest('tr');
  const id = $tr.data('id');
  if (!confirm(`Excluir tarefa #${id}?`)) return;
  API.deleteTarefa(id)
    .done(() => { $tr.remove(); })
    .fail(xhr => alert(`Erro ao excluir tarefa: ${errText(xhr)}`));
});

// salvar tarefa 
$(document).on('click', '#tblTarefas .salvar', function(){
  const $tr = $(this).closest('tr');
  const id = $tr.data('id');
  const descricao = $tr.find('.desc').text().trim();
  const responsavel = $tr.find('.resp').text().trim();
  const status = $tr.find('select.status-tarefa').val();
  API.updateTarefa(id, { descricao, responsavel, status })
    .done(() => alert('Tarefa atualizada com sucesso!'))
    .fail(xhr => alert(`Erro ao atualizar tarefa: ${errText(xhr)}`));
});


$(carregarChamados);
