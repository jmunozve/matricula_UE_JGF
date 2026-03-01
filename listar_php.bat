@echo off
setlocal enabledelayedexpansion

:: Nombre del archivo de salida
set "output=contenido_completo.txt"

:: Eliminar archivo existente si ya estaba creado
if exist "%output%" del "%output%"

:: Extensiones a procesar
set extensiones=php html js css

:: Recorre cada tipo de archivo
for %%e in (%extensiones%) do (
    echo ========== ARCHIVOS .%%e ========== >> "%output%"
    for /R %%f in (*.%%e) do (
        echo ---------- %%~nxf ---------- >> "%output%"
        type "%%f" >> "%output%"
        echo. >> "%output%"
    )
    echo. >> "%output%"
)

echo ✅ Listado generado: %output%
pause

