<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BitacoraResource\Pages;
use App\Filament\Resources\BitacoraResource\RelationManagers;
use App\Models\Bitacora;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BitacoraResource extends Resource
{
    protected static ?string $model = Bitacora::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListBitacoras::route('/'),
            'create' => Pages\CreateBitacora::route('/create'),
            'edit' => Pages\EditBitacora::route('/{record}/edit'),
        ];
    }
}
