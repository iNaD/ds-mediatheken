:: Create empty build directory
del /F /Q .\build 2> NUL
mkdir .\build 2> NUL

cd .\src

:: create the .tar.gz (aka *.host)
7z a -ttar -so mediathek INFO SynoFileHostingMediathek.php Utils Mediatheken graphql | 7z a -si -tgzip ..\build\mediathek.host

cd ..
