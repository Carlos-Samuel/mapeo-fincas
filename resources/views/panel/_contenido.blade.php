@if (filled($html))
    {{-- El renderer de Filament limpia el HTML (evita scripts inyectados). --}}
    <div class="contenido">
        {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($html)->fileAttachmentsDisk('uploads')->toHtml() !!}
    </div>
@endif
