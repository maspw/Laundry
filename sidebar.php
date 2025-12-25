<!-- FILE: sidebar.php -->
<aside class="w-64 bg-white border-r border-gray-200 flex flex-col shadow-sm z-10 flex-shrink-0">
    <div class="p-6 flex items-center gap-3 border-b border-gray-100">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">L</div>
        <h1 class="text-xl font-bold tracking-tight text-gray-900">Laundry<span class="text-blue-600">Zyngga</span></h1>
    </div>

    <nav class="flex-1 overflow-y-auto p-4 space-y-2">
        <a href="?table=dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo $current_table == 'dashboard' ? 'bg-blue-50 text-blue-700 font-semibold shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
            <i class="fa-solid fa-house w-5 text-center"></i> <span>Dashboard</span>
        </a>

        <!-- DROPDOWN 1: MASTER DATA -->
        <details class="group" open>
            <summary class="flex justify-between items-center font-semibold text-xs text-gray-400 uppercase tracking-wider px-3 py-3 hover:text-gray-600 transition">
                <span>Master Data</span>
                <i class="fa-solid fa-chevron-down chevron text-[10px]"></i>
            </summary>
            <div class="space-y-1 pl-2">
                <?php foreach($group_master as $tbl): $isActive = ($current_table == $tbl); ?>
                    <a href="?table=<?php echo $tbl; ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-200 <?php echo $isActive ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                        <i class="fa-solid <?php echo $menu_config[$tbl]['icon'] ?? 'fa-circle'; ?> w-5 text-center text-sm <?php echo $isActive ? 'text-blue-600' : 'text-gray-400'; ?>"></i>
                        <span><?php echo $menu_config[$tbl]['label'] ?? ucfirst($tbl); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </details>

        <!-- DROPDOWN 2: TRANSAKSI -->
        <details class="group" open>
            <summary class="flex justify-between items-center font-semibold text-xs text-gray-400 uppercase tracking-wider px-3 py-3 mt-2 hover:text-gray-600 transition">
                <span>Transaksi</span>
                <i class="fa-solid fa-chevron-down chevron text-[10px]"></i>
            </summary>
            <div class="space-y-1 pl-2">
                <?php foreach($group_transaksi as $tbl): $isActive = ($current_table == $tbl); ?>
                    <a href="?table=<?php echo $tbl; ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-200 <?php echo $isActive ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                        <i class="fa-solid <?php echo $menu_config[$tbl]['icon'] ?? 'fa-circle'; ?> w-5 text-center text-sm <?php echo $isActive ? 'text-blue-600' : 'text-gray-400'; ?>"></i>
                        <span><?php echo $menu_config[$tbl]['label'] ?? ucfirst(str_replace('_', ' ', $tbl)); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </details>

        <!-- DROPDOWN 3: LAPORAN -->
        <?php if(!empty($group_laporan)): ?>
        <details class="group">
            <summary class="flex justify-between items-center font-semibold text-xs text-gray-400 uppercase tracking-wider px-3 py-3 mt-2 hover:text-gray-600 transition">
                <span>Laporan & Jurnal</span>
                <i class="fa-solid fa-chevron-down chevron text-[10px]"></i>
            </summary>
            <div class="space-y-1 pl-2">
                <?php foreach($group_laporan as $tbl): $isActive = ($current_table == $tbl); ?>
                    <a href="?table=<?php echo $tbl; ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-200 <?php echo $isActive ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                        <i class="fa-solid <?php echo $menu_config[$tbl]['icon'] ?? 'fa-table'; ?> w-5 text-center text-sm <?php echo $isActive ? 'text-blue-600' : 'text-gray-400'; ?>"></i>
                        <span><?php echo $menu_config[$tbl]['label'] ?? ucfirst(str_replace('_', ' ', $tbl)); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </details>
        <?php endif; ?>
    </nav>
    <div class="p-4 border-t border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 text-white flex items-center justify-center text-xs font-bold">P</div>
            <div><p class="text-sm font-medium text-gray-900">Admin</p><p class="text-xs text-gray-500">Administrator</p></div>
        </div>
    </div>
</aside>