<?php $title = 'Token Non Valido - GymManager'; ?>

<div class="row">
    <div class="col-md-8 mx-auto text-center">
        <div class="card card-body bg-light mt-5">
            <h2><i class="fas fa-exclamation-triangle text-warning"></i> Token Non Valido</h2>
            <p>Il link di invito che hai utilizzato non è valido o è scaduto.</p>
            <p>Possibili cause:</p>
            <ul class="list-unstyled">
                <li>Il link è scaduto (validità 48 ore)</li>
                <li>Il link è già stato utilizzato per registrarsi</li>
                <li>Il token non esiste nel sistema</li>
            </ul>
            <p>Contatta l'amministratore della palestra per ricevere un nuovo invito.</p>
            <a href="<?= URLROOT ?>/login" class="btn btn-primary mt-3">Torna alla pagina di login</a>
        </div>
    </div>
</div>