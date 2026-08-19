<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$contact = new Contact($PDO);

$id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_VALIDATE_INT) : false;
if (!$id || !($contact->find($id))) {
    redirect('/');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? ''
    ];

    $errors = $contact->validate($contactData);

    $avatarPath = $contact->avatar;
    if (empty($errors)) {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName = $_FILES['avatar']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = __DIR__ . '/uploads/';

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $destPath = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    // Delete old avatar file
                    if (!empty($contact->avatar)) {
                        $oldAvatarFile = __DIR__ . '/' . $contact->avatar;
                        if (file_exists($oldAvatarFile)) {
                            unlink($oldAvatarFile);
                        }
                    }
                    $avatarPath = 'uploads/' . $newFileName;
                } else {
                    $errors['avatar'] = 'Có lỗi xảy ra khi lưu file ảnh.';
                }
            } else {
                $errors['avatar'] = 'Định dạng ảnh không hợp lệ (chỉ nhận JPG, JPEG, PNG, GIF).';
            }
        }
    }

    if (empty($errors)) {
        $contact->fill(array_merge($contactData, ['avatar' => $avatarPath]));
        if ($contact->save()) {
            redirect('/');
        } else {
            $errors['submit'] = 'Không thể cập nhật liên hệ vào CSDL.';
        }
    }
}

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>
  <?php include_once __DIR__ . '/../src/partials/navbar.php' ?>

  <!-- Main Page Content -->
  <div class="container">

    <?php
    $subtitle = 'Update your contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-12">

        <form method="post" enctype="multipart/form-data" class="col-md-6 offset-md-3">

          <input type="hidden" name="id" value="<?= $contact->id ?>">

          <!-- Name -->
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" maxlen="255" id="name" placeholder="Enter Name" value="<?= html_escape($contact->name) ?>" />

            <?php if (isset($errors['name'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['name'] ?></strong>
              </span>
            <?php endif ?>
          </div>

          <!-- Phone -->
          <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>" maxlen="255" id="phone" placeholder="Enter Phone" value="<?= html_escape($contact->phone) ?>" />

            <?php if (isset($errors['phone'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['phone'] ?></strong>
              </span>
            <?php endif ?>
          </div>

          <!-- Avatar -->
          <div class="mb-3">
            <label for="avatar" class="form-label">Avatar</label>
            <?php if (!empty($contact->avatar)): ?>
              <div class="mb-2">
                <img src="/<?= html_escape($contact->avatar) ?>" alt="Avatar" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
              </div>
            <?php endif; ?>
            <input type="file" name="avatar" class="form-control<?= isset($errors['avatar']) ? ' is-invalid' : '' ?>" id="avatar" accept="image/*" />

            <?php if (isset($errors['avatar'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['avatar'] ?></strong>
              </span>
            <?php endif ?>
          </div>

          <!-- Notes -->
          <div class="mb-3">
            <label for="notes" class="form-label">Notes </label>
            <textarea name="notes" id="notes" class="form-control<?= isset($errors['notes']) ? ' is-invalid' : '' ?>" placeholder="Enter notes (maximum character limit: 255)"><?= html_escape($contact->notes) ?></textarea>

            <?php if (isset($errors['notes'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['notes'] ?></strong>
              </span>
            <?php endif ?>
          </div>

          <!-- Submit -->
          <button type="submit" name="submit" class="btn btn-primary">Update Contact</button>
        </form>

      </div>
    </div>

  </div>

  <?php include_once __DIR__ . '/../src/partials/footer.php' ?>
</body>

</html>