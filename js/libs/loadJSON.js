function sanitizeString (value, maxLength = 100) {
    if (typeof value !== 'string') return null;

    // Entferne Steuerzeichen + trim
    // eslint-disable-next-line no-control-regex
    let sanitized = value.replace(/[\x00-\x1F\x7F]/g, '').trim();

    // Länge begrenzen
    if (sanitized.length > maxLength) {
        sanitized = sanitized.substring(0, maxLength);
    }

    return sanitized;
}

function sanitizeFilename (value) {
    if (typeof value !== 'string') return null;

    // Nur erlaubte Zeichen für Dateinamen (kein Pfad!)
    const sanitized = value.replace(/[^a-zA-Z0-9._-]/g, '');

    // Verhindere Path Traversal
    if (sanitized.includes('..')) return null;

    return sanitized;
}

function sanitizeSettings (data) {
    if (typeof data !== 'object' || data === null) {
        throw new Error('Invalid settings format');
    }

    // Prototype Pollution verhindern
    if (Object.prototype.hasOwnProperty.call(data, '__proto__')) {
        throw new Error('Prototype pollution attempt detected');
    }

    const result = {
        default: null,
        mapping: []
    };

    // --- default prüfen ---
    if (data.default && typeof data.default === 'object') {
        result.default = {
            category: sanitizeString(data.default.category),
            'icon-png': sanitizeFilename(data.default['icon-png'])
        };
    }

    // --- mapping prüfen ---
    if (Array.isArray(data.mapping)) {
        result.mapping = data.mapping
            .filter(item => typeof item === 'object' && item !== null)
            .map(item => ({
                category: sanitizeString(item.category),
                'icon-png': sanitizeFilename(item['icon-png'])
            }))
            .filter(item => item.category && item['icon-png']); // nur gültige Einträge behalten
    }

    return result;
}

async function loadSettings (url) {
    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin' // wichtig bei WP / Cookies
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const rawData = await response.json();

        // 🔐 Sanitizing + Validierung
        const safeData = sanitizeSettings(rawData);

        return safeData;

    } catch (error) {
        console.error("Can't load Settings-File: " + url, error);
        throw error;
    }
}

export { loadSettings };