const transactionDialog = document.querySelector('#transaction-dialog');
const categoryDialog = document.querySelector('#category-dialog');
const transactionForm = document.querySelector('#transaction-form');
const transactionMethod = document.querySelector('#transaction-method');
const transactionType = document.querySelector('#transaction-type');
const transactionCategory = document.querySelector('#transaction-category');

const setTransactionType = (type) => {
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
			transactionCategory.value = '';
		}
	});
};

const resetTransactionForm = () => {
	transactionForm.reset();
	transactionForm.action = transactionForm.dataset.storeUrl;
	transactionMethod.disabled = true;
	transactionMethod.value = '';
	document.querySelector('#transaction-dialog-title').textContent = 'Novo lançamento';
	document.querySelector('#transaction-submit').textContent = 'Salvar lançamento';
	setTransactionType('expense');
};

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

document.querySelectorAll('.edit-transaction').forEach((button) => {
	button.addEventListener('click', () => {
		resetTransactionForm();
		transactionForm.action = button.dataset.updateUrl;
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

document.querySelectorAll('.delete-form').forEach((form) => {
	form.addEventListener('submit', (event) => {
		if (!window.confirm('Remover este lançamento?')) {
			event.preventDefault();
		}
	});
});

document.querySelectorAll('dialog').forEach((dialog) => {
	dialog.addEventListener('click', (event) => {
		if (event.target === dialog) {
			dialog.close();
		}
	});
});

if (document.querySelector('#transaction-dialog').dataset.reopen === 'true') {
	setTransactionType(transactionType.value);
	transactionDialog.showModal();
}

if (document.querySelector('#category-dialog').dataset.reopen === 'true') {
	categoryDialog.showModal();
}
