<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VEClientesResource\Pages;
use App\Models\VEClientes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter; 
use Filament\Forms\Components\DatePicker; 

class VEClientesResource extends Resource
{
    protected static ?string $model = VEClientes::class;

    protected static ?string $navigationIcon = 'heroicon-o-users'; 

    protected static ?string $navigationGroup = 'Estadísticas';

    protected static ?string $modelLabel = 'Estadística de Cliente';

    protected static ?string $pluralModelLabel = 'Estadísticas de Clientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente_id')
                    ->label('ID Cliente')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cliente_nombre')
                    ->label('Nombre Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn(VEClientes $record): ?string => $record->cliente_nombre),
                Tables\Columns\TextColumn::make('cliente_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable() // Permite copiar el email
                    ->copyMessage('Email copiado')
                    ->icon('heroicon-m-envelope'),
                Tables\Columns\TextColumn::make('total_pedidos_realizados')
                    ->label('Pedidos Realizados')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gasto_total_cliente')
                    ->label('Gasto Total')
                    ->money('MXN')
                    ->sortable()
                    ->color('primary'), 
                Tables\Columns\TextColumn::make('fecha_ultimo_pedido')
                    ->label('Último Pedido')
                    ->dateTime('d/m/Y H:i') // O ->date('d/m/Y') si no necesitas la hora
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('fecha_registro_cliente')
                    ->label('Fecha Registro')
                    ->dateTime('d/m/Y H:i') // O ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('fecha_registro_cliente')
                    ->form([
                        DatePicker::make('registrado_desde')->label('Registrado Desde'),
                        DatePicker::make('registrado_hasta')->label('Registrado Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['registrado_desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_registro_cliente', '>=', $date),
                            )
                            ->when(
                                $data['registrado_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_registro_cliente', '<=', $date),
                            );
                    }),
                Filter::make('fecha_ultimo_pedido')
                    ->form([
                        DatePicker::make('ultimo_pedido_desde')->label('Último Pedido Desde'),
                        DatePicker::make('ultimo_pedido_hasta')->label('Último Pedido Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['ultimo_pedido_desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_ultimo_pedido', '>=', $date),
                            )
                            ->when(
                                $data['ultimo_pedido_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_ultimo_pedido', '<=', $date),
                            );
                    }),
                 Tables\Filters\QueryBuilder::make() // Filtro avanzado
                    ->constraints([
                        Tables\Filters\QueryBuilder\Constraints\NumberConstraint::make('total_pedidos_realizados')
                            ->label('Total de Pedidos Realizados'),
                        Tables\Filters\QueryBuilder\Constraints\NumberConstraint::make('gasto_total_cliente')
                             ->label('Gasto Total del Cliente (MXN)'),
                        Tables\Filters\QueryBuilder\Constraints\DateConstraint::make('fecha_registro_cliente')
                             ->label('Fecha de Registro del Cliente'),
                        Tables\Filters\QueryBuilder\Constraints\DateConstraint::make('fecha_ultimo_pedido')
                             ->label('Fecha del Último Pedido'),
                    ])
            ])
            ->actions([
                // No EditAction ni DeleteAction para una vista de solo lectura de estadísticas
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('gasto_total_cliente', 'desc'); // Como en la definición de la vista
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVEClientes::route('/'),
        ];
    }
}
