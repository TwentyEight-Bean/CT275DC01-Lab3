<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;
use CT275\Labs\Paginator;

$contact = new Contact($PDO);

$limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? (int)$_GET['limit'] : 5;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;

$total = $contact->count();
$paginator = new Paginator(totalRecords: $total, recordsPerPage: $limit, currentPage: $page);
$contacts = $contact->paginate($paginator->recordOffset, $paginator->recordsPerPage);
$pages = $paginator->getPages(3);

include_once __DIR__ . '/../src/partials/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Danh bạ</h2>
        <a href="/add.php" class="btn btn-primary"><i class="fa fa-plus"></i> Thêm liên hệ</a>
    </div>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>Avatar</th>
                <th>Tên</th>
                <th>Điện thoại</th>
                <th>Ngày tạo</th>
                <th>Ghi chú</th>
                <th class="text-center">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contacts as $item): ?>
            <tr>
                <td style="width: 70px;" class="text-center">
                    <?php if (!empty($item->avatar)): ?>
                        <img src="/<?= html_escape($item->avatar) ?>" alt="Avatar" class="rounded-circle" style="width: 45px; height: 45px; object-fit: cover;">
                    <?php else: ?>
                        <span class="badge bg-secondary">No img</span>
                    <?php endif; ?>
                </td>
                <td><?= html_escape($item->name) ?></td>
                <td><?= html_escape($item->phone) ?></td>
                <td><?= html_escape(date("d-m-Y", strtotime($item->created_at))) ?></td>
                <td><?= html_escape($item->notes) ?></td>
                <td class="text-center">
                    <a href="/edit.php?id=<?= $item->id ?>" class="btn btn-xs btn-warning">
                        <i class="fa fa-pencil"></i> Sửa
                    </a>
                    <form class="d-inline ms-1" action="/delete.php" method="POST">
                        <input type="hidden" name="id" value="<?= $item->id ?>">
                        <button type="button" class="btn btn-xs btn-danger btn-delete" data-name="<?= html_escape($item->name) ?>">
                            <i class="fa fa-trash"></i> Xóa
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Phân trang -->
    <nav class="d-flex justify-content-center">
        <ul class="pagination">
            <li class="page-item <?= $paginator->getPrevPage() ? '' : 'disabled' ?>">
                <a class="page-link" href="/?page=<?= $paginator->getPrevPage() ?>&limit=<?= $limit ?>">&laquo;</a>
            </li>
            <?php foreach ($pages as $p): ?>
            <li class="page-item <?= $paginator->currentPage == $p ? 'active' : '' ?>">
                <a class="page-link" href="/?page=<?= $p ?>&limit=<?= $limit ?>"><?= $p ?></a>
            </li>
            <?php endforeach; ?>
            <li class="page-item <?= $paginator->getNextPage() ? '' : 'disabled' ?>">
                <a class="page-link" href="/?page=<?= $paginator->getNextPage() ?>&limit=<?= $limit ?>">&raquo;</a>
            </li>
        </ul>
    </nav>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Xác nhận xóa</h5></div>
      <div class="modal-body" id="deleteModalBody">Bạn có chắc chắn muốn xóa liên hệ này?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
      </div>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../src/partials/footer.php'; ?>

<script>
let currentDeleteForm = null;
const modalEl = document.getElementById('deleteConfirmModal');
const confirmModal = new bootstrap.Modal(modalEl);

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        currentDeleteForm = this.closest('form');
        const contactName = this.getAttribute('data-name');
        document.getElementById('deleteModalBody').textContent = `Bạn có chắc chắn muốn xóa danh bạ "${contactName}"?`;
        confirmModal.show();
    });
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (currentDeleteForm) {
        currentDeleteForm.submit();
    }
});
</script>