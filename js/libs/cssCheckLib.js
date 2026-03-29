function isValidCssSize (value) {
    if (typeof value !== 'string') return false;

    // Regulärer Ausdruck für gültige CSS-Größenangaben mit Zahlen > 0
    const cssSizePattern = /^(?!0(?:px|em|rem|vw|vh|vmin|vmax|%|))(\d*\.?\d+)(px|em|rem|vw|vh|vmin|vmax|%)|auto|inherit|initial|unset$/;
    
    const match = value.trim().match(cssSizePattern);
    if (!match) return false;
    
    // Falls der Wert numerisch ist, prüfen, ob er größer als 0 ist
    if (match[1] && parseFloat(match[1]) <= 0) return false;
    
    return true;
  }

  function isValidAspectRatio (value) {
    if (typeof value !== 'string') return false;
    
    const v = value.trim();
    
    return /^\d+\.\d+$/.test(v) || /^\d+\/\d+$/.test(v) || /^\d+$/.test(v);
  } 

  export { isValidCssSize, isValidAspectRatio };