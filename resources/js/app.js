const transactionDialog = document.querySelector('#transaction-dialog');
const categoryDialog = document.querySelector('#category-dialog');
const transactionForm = document.querySelector('#transaction-form');
const transactionMethod = document.querySelector('#transaction-method');
const transactionType = document.querySelector('#transaction-type');
const transactionCategory = document.querySelector('#transaction-category');

// Atualiza o tipo selecionado e oculta categorias que não correspondem a ele.
const setTransactionType = (type) => {
	// O valor oculto é o que será enviado no formulário; os botões só controlam a aparência.
	transactionType.value = type;

	document.querySelectorAll('[data-type-option]').forEach((option) => {
		const isSelected = option.dataset.typeOption === type;
		option.classList.toggle('is-selected', isSelected);
		option.setAttribute('aria-pressed', String(isSelected));
	});

	transactionCategory.querySelectorAll('[data-category-type]').forEach((option) => {
		const isAvailable = option.dataset.categoryType === type;
		option.hidden = !isAvailable;

		if (!isAvailable && option.selected) {
			// Não deixa uma categoria de receita selecionada junto com uma despesa (ou vice-versa).
			transactionCategory.value = '';
		}
	});
};

// Limpa o modal e prepara seus campos para a criação de um novo lançamento.
const resetTransactionForm = () => {
	// Limpa dados antigos e devolve a ação do formulário para a rota de criação.
	transactionForm.reset();
	transactionForm.action = transactionForm.dataset.storeUrl;
	transactionMethod.disabled = true;
	transactionMethod.value = '';
	document.querySelector('#transaction-dialog-title').textContent = 'Novo lançamento';
	document.querySelector('#transaction-submit').textContent = 'Salvar lançamento';
	setTransactionType('expense');
};

// Abre os modais de lançamento e categoria acionados pela navegação.
document.querySelectorAll('[data-open-transaction]').forEach((button) => {
	button.addEventListener('click', () => {
		resetTransactionForm();
		transactionDialog.showModal();
	});
});

document.querySelectorAll('[data-open-categories]').forEach((button) => {
	button.addEventListener('click', () => categoryDialog.showModal());
});

document.querySelectorAll('[data-close-dialog]').forEach((button) => {
	button.addEventListener('click', () => button.closest('dialog').close());
});

document.querySelectorAll('[data-type-option]').forEach((option) => {
	option.addEventListener('click', () => setTransactionType(option.dataset.typeOption));
});

// Preenche o formulário compartilhado com os dados do lançamento selecionado para edição.
document.querySelectorAll('.edit-transaction').forEach((button) => {
	button.addEventListener('click', () => {
		resetTransactionForm();
		transactionForm.action = button.dataset.updateUrl;
		// Formulários HTML não enviam PUT diretamente; o campo oculto pede essa conversão ao Laravel.
		transactionMethod.disabled = false;
		transactionMethod.value = 'PUT';
		document.querySelector('#transaction-dialog-title').textContent = 'Editar lançamento';
		document.querySelector('#transaction-submit').textContent = 'Salvar alterações';
		document.querySelector('#transaction-description').value = button.dataset.description;
		document.querySelector('#transaction-amount').value = button.dataset.amount;
		document.querySelector('#transaction-date').value = button.dataset.date;
		document.querySelector('#transaction-notes').value = button.dataset.notes;
		setTransactionType(button.dataset.type);
		transactionCategory.value = button.dataset.categoryId;
		transactionDialog.showModal();
	});
});

// Normaliza campos, valida o formulário no navegador e evita envios repetidos.
document.querySelectorAll('form').forEach((form) => {
	if (form.method.toLowerCase() !== 'post') {
		return;
	}

	const trimInputs = form.querySelectorAll('[data-trim]');

	trimInputs.forEach((input) => {
		input.addEventListener('blur', () => {
			input.value = input.value.trim();
		});
	});

	form.addEventListener('submit', (event) => {
		trimInputs.forEach((input) => {
			input.value = input.value.trim();
		});

		if (!form.checkValidity()) {
			// Impede o envio enquanto houver campos inválidos e pede ao navegador que os destaque.
			event.preventDefault();
			form.reportValidity();

			return;
		}

		const submitButton = form.querySelector('[type="submit"]');

		if (submitButton && !submitButton.disabled) {
			submitButton.disabled = true;
			submitButton.textContent = submitButton.dataset.loadingText
				?? (form.classList.contains('delete-form') ? 'Removendo...' : 'Salvando...');
		}
	});
});

// Permite fechar avisos e modais usando seus controles ou clicando fora do conteúdo.
document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
	button.addEventListener('click', () => button.closest('.flash-message').remove());
});

document.querySelectorAll('dialog').forEach((dialog) => {
	dialog.addEventListener('click', (event) => {
		if (event.target === dialog) {
			dialog.close();
		}
	});
});

// Reabre o modal que teve erro de validação e direciona o foco ao primeiro campo inválido.
if (document.querySelector('#transaction-dialog').dataset.reopen === 'true') {
	// Ao voltar de um erro, preserva o modal aberto para a pessoa corrigir o campo.
	setTransactionType(transactionType.value);
	transactionDialog.showModal();
	transactionDialog.querySelector('[aria-invalid="true"]')?.focus();
}

if (document.querySelector('#category-dialog').dataset.reopen === 'true') {
	categoryDialog.showModal();
	categoryDialog.querySelector('[aria-invalid="true"]')?.focus();
}

// Atalho "N" abre um lançamento novo somente quando o usuário não está digitando em um campo.
document.addEventListener('keydown', (event) => {
	const activeElement = document.activeElement;
	const isEditing = activeElement instanceof HTMLElement
		&& (activeElement.isContentEditable || ['INPUT', 'SELECT', 'TEXTAREA'].includes(activeElement.tagName));

	if (
		event.key.toLowerCase() === 'n'
		&& !event.altKey
		&& !event.ctrlKey
		&& !event.metaKey
		&& !event.repeat
		&& !isEditing
		&& !document.querySelector('dialog[open]')
	) {
		// Não intercepta N quando a pessoa está digitando, usando modificadores ou com outro modal aberto.
		event.preventDefault();
		document.querySelector('[data-open-transaction]')?.click();
	}
});
