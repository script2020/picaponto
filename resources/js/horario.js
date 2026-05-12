import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('calendario');
    if (!el) return;

    const eventosUrl = el.dataset.eventosUrl;
    const updateBase = el.dataset.updateUrl; // …/horario/__DATA__
    const readonly   = el.dataset.readonly === 'true';

    const modal              = document.getElementById('modal-horario');
    const modalTitulo        = document.getElementById('modal-titulo');
    const inputAtivo         = document.getElementById('modal-ativo');
    const inputInicio        = document.getElementById('modal-inicio');
    const inputFim           = document.getElementById('modal-fim');
    const camposHoras        = document.getElementById('modal-campos-horas');
    const inputReposicao     = document.getElementById('modal-reposicao');
    const campoReposicao     = document.getElementById('modal-campo-reposicao');
    const inputDataReposicao = document.getElementById('modal-data-reposicao');
    const formModal          = document.getElementById('form-modal');

    const meses = ['janeiro','fevereiro','março','abril','maio','junho',
                   'julho','agosto','setembro','outubro','novembro','dezembro'];
    const diasSemana = ['domingo','segunda-feira','terça-feira','quarta-feira',
                        'quinta-feira','sexta-feira','sábado'];

    function formatarData(date) {
        return `${diasSemana[date.getDay()]}, ${date.getDate()} de ${meses[date.getMonth()]} de ${date.getFullYear()}`;
    }

    function toDateString(date) {
        // YYYY-MM-DD local (sem desvio de fuso)
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    if (!readonly) {
        inputAtivo.addEventListener('change', () => {
            camposHoras.style.display = inputAtivo.checked ? '' : 'none';
        });

        const fechar = () => modal.classList.add('hidden');
        document.getElementById('modal-fechar').addEventListener('click', fechar);
        document.getElementById('modal-fechar-btn').addEventListener('click', fechar);
        modal.addEventListener('click', e => { if (e.target === modal) fechar(); });

        // Impede submissão se a data não foi definida corretamente
        formModal.addEventListener('submit', e => {
            if (formModal.action.includes('__DATA__') || !formModal.action) {
                e.preventDefault();
            }
        });
    }

    function abrirModal(date, info) {
        modalTitulo.textContent = formatarData(date);
        inputAtivo.checked  = info.ativo ?? false;
        inputInicio.value   = info.hora_inicio ?? '09:00';
        inputFim.value      = info.hora_fim    ?? '17:00';
        camposHoras.style.display = inputAtivo.checked ? '' : 'none';

        inputReposicao.checked    = info.reposicao ?? false;
        inputDataReposicao.value  = info.data_reposicao ?? '';
        campoReposicao.classList.toggle('hidden', !inputReposicao.checked);

        formModal.action = updateBase.replace('__DATA__', toDateString(date));
        modal.classList.remove('hidden');
    }

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: 'pt',
        firstDay: 1,
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  '',
        },
        height: 'auto',
        events: eventosUrl,

        // Clicar num evento existente
        eventClick: readonly ? undefined : function({ event }) {
            const date = event.start;
            const info = {
                ativo:           true,
                hora_inicio:     event.extendedProps.hora_inicio,
                hora_fim:        event.extendedProps.hora_fim,
                reposicao:       event.extendedProps.reposicao ?? false,
                data_reposicao:  event.extendedProps.data_reposicao ?? '',
            };
            abrirModal(date, info);
        },

        // Clicar num dia vazio
        dateClick: readonly ? undefined : function({ date }) {
            abrirModal(date, { ativo: false });
        },
    });

    calendar.render();
});
