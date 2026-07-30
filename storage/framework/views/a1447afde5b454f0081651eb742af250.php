<?php $editing = $category->exists; ?>
<?php $__env->startSection('title', $editing ? 'Edit Category' : 'New Category'); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e($editing ? route('admin.categories.update', $category) : route('admin.categories.store')); ?>" enctype="multipart/form-data">
  <?php echo csrf_field(); ?>
  <?php if($editing): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

  <div class="flex items-center justify-between mb-6">
    <div>
      <a href="<?php echo e(route('admin.categories.index')); ?>" class="text-sm text-gray-500 hover:text-primary">&larr; Back</a>
      <h2 class="text-xl font-bold mt-1"><?php echo e($editing ? 'Edit Category' : 'New Category'); ?></h2>
    </div>
    <button class="btn-primary">Save</button>
  </div>

  <div class="max-w-2xl space-y-6">
    <div class="card p-5 space-y-4">
      <h3 class="font-semibold">Details</h3>
      <div class="grid grid-cols-[1fr_100px] gap-4">
        <div><label class="lbl">Name</label><input name="name" class="inp" value="<?php echo e(old('name', $category->name)); ?>" required /></div>
        <div><label class="lbl">Icon (emoji)</label><input name="icon" class="inp" value="<?php echo e(old('icon', $category->icon)); ?>" placeholder="👕" /></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="lbl">Slug (blank = auto)</label><input name="slug" class="inp" value="<?php echo e(old('slug', $category->slug)); ?>" /></div>
        <div><label class="lbl">Position</label><input name="position" type="number" class="inp" value="<?php echo e(old('position', $category->position ?? 0)); ?>" /></div>
      </div>
      <div><label class="lbl">Description</label><textarea name="description" class="inp" rows="2"><?php echo e(old('description', $category->description)); ?></textarea></div>
      <div>
        <label class="lbl">Image</label>
        <div class="flex items-center gap-3">
          <?php if($category->image): ?><img src="<?php echo e($category->imageUrl()); ?>" class="h-14 w-14 rounded-lg object-cover" alt=""><?php endif; ?>
          <input name="image_file" type="file" accept="image/*" class="text-sm" />
        </div>
      </div>
      <div class="space-y-2 pt-2 border-t border-gray-100">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" class="accent-primary" <?php if(old('is_active', $category->is_active ?? true)): echo 'checked'; endif; ?> /> Active (visible on storefront)</label>
        <label class="flex items-center gap-2 text-sm font-medium text-amber-900"><input type="checkbox" name="is_featured" value="1" class="accent-primary" <?php if(old('is_featured', $category->is_featured ?? false)): echo 'checked'; endif; ?> /> ★ Featured on Homepage (Displays dedicated category product section)</label>
      </div>
    </div>

    <div class="card p-5 space-y-3">
      <h3 class="font-semibold">SEO</h3>
      <div><label class="lbl">Meta title</label><input name="meta_title" class="inp" value="<?php echo e(old('meta_title', $category->meta_title)); ?>" /></div>
      <div><label class="lbl">Meta description</label><textarea name="meta_description" class="inp" rows="2"><?php echo e(old('meta_description', $category->meta_description)); ?></textarea></div>
      <div><label class="lbl">Meta keywords (comma-separated)</label><textarea name="meta_keywords" class="inp" rows="2"><?php echo e(old('meta_keywords', $category->meta_keywords)); ?></textarea></div>
    </div>
  </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/admin/categories/form.blade.php ENDPATH**/ ?>