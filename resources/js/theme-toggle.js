const themePreferenceKey = 'saldo-theme';
const themeToggle = document.querySelector('[data-theme-toggle]');
const savedTheme = localStorage.getItem(themePreferenceKey);

if (savedTheme === 'dark' || savedTheme === 'light') {
	document.documentElement.dataset.theme = savedTheme;
}

if (themeToggle) {
	const themeIcon = themeToggle.querySelector('[data-theme-icon]');
	const themeLabel = themeToggle.querySelector('[data-theme-label]');

	const updateThemeToggle = () => {
		const isDarkTheme = document.documentElement.dataset.theme === 'dark';

		themeToggle.setAttribute('aria-pressed', String(isDarkTheme));
		themeToggle.setAttribute('aria-label', `Ativar tema ${isDarkTheme ? 'claro' : 'escuro'}`);
		themeIcon.textContent = isDarkTheme ? '☼' : '☾';
		themeLabel.textContent = `Modo ${isDarkTheme ? 'claro' : 'escuro'}`;
	};

	updateThemeToggle();

	themeToggle.addEventListener('click', () => {
		const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

		document.documentElement.dataset.theme = nextTheme;
		localStorage.setItem(themePreferenceKey, nextTheme);
		updateThemeToggle();
	});
}
