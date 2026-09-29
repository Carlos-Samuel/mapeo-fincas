<?php

namespace App\Filament\Resources\Fincas\Pages;

use App\Filament\Resources\Fincas\FincaResource;
use App\Filament\Resources\Lotes\LoteResource;
use App\Filament\Resources\Puntos\PuntoResource;
use App\Filament\Resources\Recorridos\RecorridoResource;
use App\Filament\Resources\Rutas\RutaResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use App\Models\Tipo;
use App\Support\ImportadorPuntosGpx;
use Illuminate\Support\Facades\Storage;

class EditFinca extends EditRecord
{
    protected static string $resource = FincaResource::class;

    protected function getHeaderActions(): array
    {
        $finca = $this->getRecord();

        return [
            Action::make('agregarLote')
                ->label('Agregar lote')
                ->icon(Heroicon::OutlinedSquares2x2)
                ->url(LoteResource::getUrl('create', ['finca_id' => $finca->id])),
            Action::make('agregarPunto')
                ->label('Agregar punto')
                ->icon(Heroicon::OutlinedMapPin)
                ->color('gray')
                ->url(PuntoResource::getUrl('create', ['finca_id' => $finca->id])),
            Action::make('agregarRuta')
                ->label('Agregar ruta')
                ->icon(Heroicon::OutlinedArrowTrendingUp)
                ->color('gray')
                ->url(RutaResource::getUrl('create', ['finca_id' => $finca->id])),
            ActionGroup::make([
                Action::make('importarPuntosGpx')
                    ->label('Importar puntos (GPX)')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->modalHeading('Importar puntos desde un GPX')
                    ->modalDescription('Cada waypoint del archivo se crea como un punto de esta finca. Los que tengan el mismo nombre que un punto existente se omiten.')
                    ->modalSubmitActionLabel('Importar')
                    ->schema([
                        FileUpload::make('archivo')
                            ->label('Archivo GPX')
                            ->disk('local')
                            ->directory('gpx-temporal')
                            ->maxSize(10240)
                            ->required(),
                        Select::make('tipo_id')
                            ->label('Tipo para los puntos importados')
                            ->options(fn () => Tipo::query()->paraPuntos()->pluck('nombre', 'id'))
                            ->placeholder('Sin tipo (se puede asignar después)'),
                    ])
                    ->action(fn (array $data) => $this->importarPuntosGpx($data)),
                Action::make('agregarRecorrido')
                    ->label('Agregar recorrido')
                    ->icon(Heroicon::OutlinedFlag)
                    ->url(RecorridoResource::getUrl('create', ['finca_id' => $finca->id])),
                Action::make('ver')
                    ->label('Ver mapa público')
                    ->icon(Heroicon::OutlinedMap)
                    ->url(route('fincas.show', $finca), shouldOpenInNewTab: true),
                Action::make('verLotes')
                    ->label('Ver lotes de esta finca')
                    ->url(LoteResource::getUrl('index', ['filters' => ['finca' => ['value' => $finca->id]]])),
                Action::make('verPuntos')
                    ->label('Ver puntos de esta finca')
                    ->url(PuntoResource::getUrl('index', ['filters' => ['finca' => ['value' => $finca->id]]])),
                Action::make('verRutas')
                    ->label('Ver rutas de esta finca')
                    ->url(RutaResource::getUrl('index', ['filters' => ['finca' => ['value' => $finca->id]]])),
                Action::make('verRecorridos')
                    ->label('Ver recorridos de esta finca')
                    ->url(RecorridoResource::getUrl('index', ['filters' => ['finca' => ['value' => $finca->id]]])),
                DeleteAction::make()
                    ->modalDescription('Se borrarán también todos sus lotes, puntos, rutas, recorridos e imágenes. Esta acción no se puede deshacer.'),
            ]),
        ];
    }

    /** Crea puntos a partir de los waypoints del GPX subido y borra el archivo temporal. */
    protected function importarPuntosGpx(array $data): void
    {
        $disco = Storage::disk('local');
        $ruta = $data['archivo'];

        try {
            $r = ImportadorPuntosGpx::importar($this->getRecord(), $disco->get($ruta) ?? '', $data['tipo_id'] ?? null);
        } catch (\InvalidArgumentException $e) {
            Notification::make()->danger()->title('No se pudo importar')->body($e->getMessage())->send();

            return;
        } finally {
            $disco->delete($ruta);
        }

        $detalle = collect([
            $r['omitidos'] ? "{$r['omitidos']} omitidos porque ya existía un punto con ese nombre" : null,
            $r['fuera'] ? "{$r['fuera']} quedaron por fuera del contorno de la finca" : null,
        ])->filter()->join('; ');

        Notification::make()
            ->title("Se crearon {$r['creados']} puntos")
            ->body($detalle ?: null)
            ->{$r['fuera'] ? 'warning' : 'success'}()
            ->send();
    }
}
