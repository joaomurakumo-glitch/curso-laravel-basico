const phoneInput = document.querySelector('#registration-phone');

if (phoneInput) {
	phoneInput.addEventListener('input', () => {
		const digits = phoneInput.value.replace(/\D/g, '').slice(0, 11);

		if (digits.length <= 2) {
			phoneInput.value = digits ? `(${digits}` : '';

			return;
		}

		const areaCode = digits.slice(0, 2);
		const subscriber = digits.slice(2);
		phoneInput.value = `(${areaCode}) ${subscriber.slice(0, 5)}`;

		if (subscriber.length > 5) {
			phoneInput.value += `-${subscriber.slice(5)}`;
		}
	});
}

const dateRangeFilter = document.querySelector('#date-range-filter');

if (dateRangeFilter) {
	const startDate = dateRangeFilter.querySelector('[name="start_date"]');
	const endDate = dateRangeFilter.querySelector('[name="end_date"]');
	const updateRequiredDates = () => {
		const hasDateRange = startDate.value !== '' || endDate.value !== '';
		startDate.required = hasDateRange;
		endDate.required = hasDateRange;
	};

	startDate.addEventListener('input', updateRequiredDates);
	endDate.addEventListener('input', updateRequiredDates);
}

document.querySelectorAll('form[data-trim-form]').forEach((form) => {
	form.addEventListener('submit', () => {
		form.querySelectorAll('[data-trim]').forEach((input) => {
			input.value = input.value.trim();
		});
	});
});

const currencyFormatter = new Intl.NumberFormat('pt-BR', {
	maximumFractionDigits: 0,
});

document.querySelectorAll('[data-discount-form]').forEach((form) => {
	const quantityInput = form.querySelector('[data-discount-quantity]');
	const discountInput = form.querySelector('[data-discount-percent]');
	const totalOutput = form.querySelector('[data-discount-total]');
	const unitPriceCents = BigInt(Math.round(Number(form.dataset.unitPrice) * 100));

	const updateTotal = () => {
		const quantity = Number(quantityInput.value);
		const discount = Number(discountInput.value);

		if (!Number.isInteger(quantity) || quantity < 1 || !Number.isFinite(discount) || discount < 0 || discount > 30) {
			totalOutput.textContent = 'R$ 0,00';

			return;
		}

		const discountHundredths = BigInt(Math.round(discount * 100));
		const subtotalCents = unitPriceCents * BigInt(quantity);
		const totalCents = (subtotalCents * (10000n - discountHundredths) + 5000n) / 10000n;
		const wholeAmount = currencyFormatter.format(totalCents / 100n);
		const cents = (totalCents % 100n).toString().padStart(2, '0');
		totalOutput.textContent = `R$ ${wholeAmount},${cents}`;
	};

	quantityInput.addEventListener('input', updateTotal);
	discountInput.addEventListener('input', updateTotal);
	updateTotal();
});
