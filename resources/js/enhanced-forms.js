const phoneInput = document.querySelector('#registration-phone');

// Aplica a máscara do celular durante a digitação sem ultrapassar os 11 dígitos.
if (phoneInput) {
	phoneInput.addEventListener('input', () => {
		const digits = phoneInput.value.replace(/\D/g, '').slice(0, 11);

		if (digits.length <= 2) {
			// Enquanto o DDD está incompleto, mostra apenas o parêntese de abertura e os números digitados.
			phoneInput.value = digits ? `(${digits}` : '';

			return;
		}

		const areaCode = digits.slice(0, 2);
		const subscriber = digits.slice(2);
		// Separa DDD e número para inserir a máscara sem alterar os dígitos informados.
		phoneInput.value = `(${areaCode}) ${subscriber.slice(0, 5)}`;

		if (subscriber.length > 5) {
			phoneInput.value += `-${subscriber.slice(5)}`;
		}
	});
}

const dateRangeFilter = document.querySelector('#date-range-filter');

// Ao iniciar um filtro personalizado, exige tanto a data inicial quanto a final.
if (dateRangeFilter) {
	const startDate = dateRangeFilter.querySelector('[name="start_date"]');
	const endDate = dateRangeFilter.querySelector('[name="end_date"]');
	const updateRequiredDates = () => {
		const hasDateRange = startDate.value !== '' || endDate.value !== '';
		// Um único campo preenchido significa que o filtro de intervalo está em uso.
		startDate.required = hasDateRange;
		endDate.required = hasDateRange;
	};

	startDate.addEventListener('input', updateRequiredDates);
	endDate.addEventListener('input', updateRequiredDates);
}

// Remove espaços nas extremidades antes de enviar os formulários configurados para isso.
document.querySelectorAll('form[data-trim-form]').forEach((form) => {
	form.addEventListener('submit', () => {
		form.querySelectorAll('[data-trim]').forEach((input) => {
			input.value = input.value.trim();
		});
	});
});
