import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Dev/**/*.php',
        './resources/views/filament/dev/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
