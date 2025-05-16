<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-building mr-2"></i><?= __('Impostazioni Palestra') ?></h2>
            <p><?= __('Personalizza le informazioni della tua palestra') ?></p>
        </div>
        
        <div class="card-body">
            <?php flash(); ?>
            
            <form action="<?= URLROOT ?>/gym-settings/save" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="gym_name"><?= __('Nome Palestra') ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($data['errors']['gym_name']) ? 'is-invalid' : '' ?>" 
                                   id="gym_name" name="gym_name" 
                                   value="<?= $data['gymSettings']['gym_name'] ?? '' ?>">
                            <?php if (isset($data['errors']['gym_name'])) : ?>
                                <div class="invalid-feedback"><?= $data['errors']['gym_name'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="logo"><?= __('Logo Palestra') ?></label>
                            <?php if (!empty($data['gymSettings']['logo_path'])) : ?>
                                <div class="mb-2">
                                    <img src="<?= $data['gymSettings']['logo_path'] ?>" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control-file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif">
                            <small class="text-muted"><?= __('Formati supportati: JPG, PNG, GIF. Dimensione massima: 2MB') ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="address"><?= __('Indirizzo') ?></label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?= $data['gymSettings']['address'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="city"><?= __('Città') ?></label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?= $data['gymSettings']['city'] ?? '' ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone"><?= __('Telefono') ?></label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?= $data['gymSettings']['phone'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email"><?= __('Email') ?></label>
                            <input type="email" class="form-control <?= isset($data['errors']['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" 
                                   value="<?= $data['gymSettings']['email'] ?? '' ?>">
                            <?php if (isset($data['errors']['email'])) : ?>
                                <div class="invalid-feedback"><?= $data['errors']['email'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> <?= __('Salva Impostazioni') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>