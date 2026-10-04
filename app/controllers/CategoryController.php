<?php
/**
 * Hesabdar - Category Controller
 * Manages expense and income categories.
 */

require_once __DIR__ . '/Controller.php';
require_once dirname(__DIR__) . '/models/Category.php';

class CategoryController extends Controller {
    private Category $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->categoryModel = new Category();
    }

    /**
     * List all categories (Income & Expense)
     */
    public function index(): void {
        $userId = $this->requireAuth();
        $expenseCategories = $this->categoryModel->getAllForUser($userId, 'expense', true);
        $incomeCategories = $this->categoryModel->getAllForUser($userId, 'income', true);

        $this->render('categories/index', [
            'pageTitle' => 'دسته‌بندی‌ها',
            'expenseCategories' => $expenseCategories,
            'incomeCategories' => $incomeCategories,
        ]);
    }

    /**
     * Store new category
     */
    public function store(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'expense';
        $color = trim($_POST['color'] ?? '#64748b');
        $icon = trim($_POST['icon'] ?? 'tag');

        if (empty($name) || !in_array($type, ['income', 'expense'], true)) {
            Session::setFlash('error', 'اطلاعات دسته‌بندی ناقص یا نامعتبر است.');
            $this->redirect('categories');
        }

        $this->categoryModel->create([
            'user_id' => $userId,
            'name' => $name,
            'type' => $type,
            'color' => $color,
            'icon' => $icon,
            'status' => 'active'
        ]);

        Session::setFlash('success', 'دسته‌بندی با موفقیت افزوده شد.');
        $this->redirect('categories');
    }

    /**
     * Update existing category
     */
    public function update(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $id = (int)($_POST['id'] ?? 0);
        $category = $this->categoryModel->findById($id, $userId);
        if (!$category) {
            Session::setFlash('error', 'دسته‌بندی مورد نظر یافت نشد.');
            $this->redirect('categories');
        }

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? $category['type'];
        $color = trim($_POST['color'] ?? $category['color']);
        $icon = trim($_POST['icon'] ?? $category['icon']);
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            Session::setFlash('error', 'نام دسته‌بندی الزامی است.');
            $this->redirect('categories');
        }

        $this->categoryModel->update($id, $userId, [
            'name' => $name,
            'type' => $type,
            'color' => $color,
            'icon' => $icon,
            'status' => $status
        ]);

        Session::setFlash('success', 'دسته‌بندی با موفقیت ویرایش شد.');
        $this->redirect('categories');
    }

    /**
     * Delete or archive category
     */
    public function delete(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->categoryModel->deleteOrArchive($id, $userId);
            Session::setFlash('success', 'عملیات با موفقیت انجام شد.');
        }

        $this->redirect('categories');
    }
}
