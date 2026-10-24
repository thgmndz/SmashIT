<?php
$src = 'C:/Users/Thiago Mendez/.gemini/antigravity-ide/brain/73cf67b9-696c-4326-a1ab-4e02062554c3/media__1786556380807.png';
$dest = 'c:/xampp/htdocs/SmashIT/logo_light.png';

if (file_exists($src)) {
    if (copy($src, $dest)) {
        echo "[+] Logo light copiado correctamente a $dest\n";
    } else {
        echo "[-] Error al copiar el archivo.\n";
    }
} else {
    echo "[-] Archivo de origen no encontrado en $src\n";
}
