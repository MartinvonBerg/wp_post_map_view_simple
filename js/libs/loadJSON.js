async function loadSettings (url) {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error("Can't load Settings-File: " + url, error);
        throw error; // Fehler weiterwerfen, um ihn außerhalb der Funktion zu behandeln
    }
}

export { loadSettings };