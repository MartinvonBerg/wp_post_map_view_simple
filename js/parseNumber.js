function parseLocalizedInt(input, locale = navigator.language) {
    // Normalisieren: Entferne nicht-numerische Zeichen außer Dezimal- und Tausendertrennzeichen
    const formatter = new Intl.NumberFormat(locale);
    const parts = formatter.formatToParts(12345.6);
    
    let groupSeparator = '', decimalSeparator = '';
    for (const part of parts) {
        if (part.type === 'group') groupSeparator = part.value;
        if (part.type === 'decimal') decimalSeparator = part.value;
    }

    // Entferne Tausendertrennzeichen und alles nach dem Dezimalzeichen
    const normalized = input
        .replace(new RegExp(`\\${groupSeparator}`, 'g'), '') // Tausendertrennzeichen entfernen
        .split(decimalSeparator)[0]; // Alles nach dem Dezimalzeichen abschneiden

    return parseInt(normalized, 10) || 0; // Sichere Umwandlung in Integer, Fallback auf 0
}

export { parseLocalizedInt };