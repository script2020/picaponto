<?php

use App\Http\Controllers\HorarioSemanaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistoHorasController;
use App\Http\Controllers\TarefaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/', [RegistoHorasController::class, 'index'])->name('registos.index');
    Route::post('/registos/entrada', [RegistoHorasController::class, 'registarEntrada'])->name('registos.entrada');
    Route::post('/registos/{registo}/saida', [RegistoHorasController::class, 'registarSaida'])->name('registos.saida');

    Route::get('/tarefas', [TarefaController::class, 'index'])->name('tarefas.index');
    Route::get('/tarefas/criar', [TarefaController::class, 'create'])->name('tarefas.create');
    Route::post('/tarefas', [TarefaController::class, 'store'])->name('tarefas.store');
    Route::put('/tarefas/{tarefa}', [TarefaController::class, 'update'])->name('tarefas.update');
    Route::delete('/tarefas/{tarefa}', [TarefaController::class, 'destroy'])->name('tarefas.destroy');
    Route::post('/tarefas/{tarefa}/comentarios', [TarefaController::class, 'adicionarComentario'])->name('tarefas.comentarios');
    Route::post('/tarefas/{tarefa}/comentarios/{comentarioId}/resposta', [TarefaController::class, 'adicionarRespostaComentario'])->name('tarefas.resposta');
    Route::post('/tarefas/{tarefa}/avancar', [TarefaController::class, 'avancarEstado'])->name('tarefas.avancar');

    Route::get('/horario', [HorarioSemanaController::class, 'index'])->name('horario.index');
    Route::get('/horario/eventos', [HorarioSemanaController::class, 'eventos'])->name('horario.eventos');
    Route::put('/horario/{data}', [HorarioSemanaController::class, 'updateData'])->name('horario.updateData');

    Route::middleware('admin')->group(function () {
        Route::get('/admin/registos', [RegistoHorasController::class, 'admin'])->name('registos.admin');
        Route::get('/admin/registos/{user}/pdf', [RegistoHorasController::class, 'exportarPdf'])->name('registos.user.pdf');

        Route::get('/admin/horario/{user}', [HorarioSemanaController::class, 'adminVer'])->name('horario.admin.ver');
        Route::get('/admin/horario/{user}/eventos', [HorarioSemanaController::class, 'adminEventos'])->name('horario.admin.eventos');
    });
});

require __DIR__.'/auth.php';
