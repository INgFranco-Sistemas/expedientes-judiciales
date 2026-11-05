import { ref, readonly } from 'vue'

const STORAGE_KEY = 'sigep-theme'
const VALID_THEMES = ['light', 'dark', 'auto']

const savedTheme = localStorage.getItem(STORAGE_KEY)

const theme = ref(
    VALID_THEMES.includes(savedTheme) ? savedTheme : 'auto'
)

const systemPreference = window.matchMedia(
    '(prefers-color-scheme: dark)'
)

function applyTheme() {
    const isDark =
        theme.value === 'dark' ||
        (theme.value === 'auto' && systemPreference.matches)

    document.documentElement.classList.toggle(
        'sigep-dark',
        isDark
    )

    document.documentElement.style.colorScheme =
        isDark ? 'dark' : 'light'
}

function setTheme(value) {
    if (!VALID_THEMES.includes(value)) return

    theme.value = value
    localStorage.setItem(STORAGE_KEY, value)

    applyTheme()
}

systemPreference.addEventListener('change', () => {
    if (theme.value === 'auto') {
        applyTheme()
    }
})

applyTheme()

export function useTheme() {
    return {
        theme: readonly(theme),
        setTheme
    }
}