function getIconMappingArray(data) {
    // Überprüfen, ob data ein Array ist und 'mapping' enthält
    if (typeof data !== 'object' || !data.mapping || typeof data.mapping !== 'object') {
        return []; // Leeres Array zurückgeben, wenn die Eingabe ungültig ist
    }

    const allIcons = [];

    // Mapping-Daten verarbeiten
    for (const key in data.mapping) {
        if (data.mapping.hasOwnProperty(key)) {
            const mapping = data.mapping[key];
            const iconPng = mapping['icon-png'] || data.default['icon-png'];
            const icon = mapping['icon'] || data.default['icon'];
            const category = mapping['category'] || data.default['category'];

            allIcons.push([iconPng, icon, category]);
        }
    }

    return allIcons;
}

export { getIconMappingArray };