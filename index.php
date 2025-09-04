<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mtwara Gas Plant Maintenance Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; }
        .modal-overlay { background-color: rgba(0, 0, 0, 0.5); }
        .modal-content { max-height: 80vh; overflow-y: auto; }
        .tab-button.active { background-color: #f3f4f6; color: #1f2937; font-weight: 700; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .action-button {
            transition: all 0.2s ease-in-out;
            transform: scale(1);
        }
        .action-button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .text-blue-900 { color: #1e3a8a; }
        .bg-blue-900 { background-color: #1e3a8a; }
        .hover\:bg-blue-800:hover { background-color: #1e40af; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <div id="login-container" class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-sm">
            <h2 class="text-3xl font-bold text-center text-blue-900 mb-6">Mtwara Gas Plant</h2>
            <form id="login-form">
                <div class="mb-4">
                    <label for="username" class="block font-semibold mb-1">Username:</label>
                    <input type="text" id="username" name="username" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block font-semibold mb-1">Password:</label>
                    <input type="password" id="password" name="password" class="w-full p-2 border rounded-lg" required>
                </div>
                <button type="submit" class="w-full bg-blue-900 text-white p-2 rounded-lg font-bold hover:bg-blue-800 transition-colors">Log In</button>
                <p id="login-error" class="text-red-500 text-sm text-center mt-4 hidden">Invalid username or password.</p>
            </form>
        </div>
    </div>

    <div id="app-container" class="min-h-screen flex flex-col hidden">
        <header class="bg-blue-900 text-white p-6 shadow-lg flex justify-between items-center">
            <h1 class="text-3xl font-bold">  Mtwara Gas Plant Maintenance Management System</h1>
            <button onclick="logout()" class="bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-700 transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        </header>

        <nav class="bg-gray-200 shadow-md">
            <div class="flex flex-wrap justify-center space-x-2 p-2">
                <button id="nav-work-orders" class="tab-button px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-300 transition duration-300 ease-in-out active">
                    <i class="fas fa-tools mr-2"></i>Work Orders
                </button>
                <button id="nav-equipment" class="tab-button px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-300 transition duration-300 ease-in-out">
                    <i class="fas fa-cogs mr-2"></i>Equipment
                </button>
                <button id="nav-inventory" class="tab-button px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-300 transition duration-300 ease-in-out">
                    <i class="fas fa-warehouse mr-2"></i>Inventory
                </button>
                <button id="nav-staff" class="tab-button px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-300 transition duration-300 ease-in-out">
                    <i class="fas fa-users mr-2"></i>Staff Leave
                </button>
                <button id="nav-reports" class="tab-button px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-300 transition duration-300 ease-in-out">
                    <i class="fas fa-chart-line mr-2"></i>Reports
                </button>
            </div>
        </nav>

        <main class="flex-grow p-4 md:p-8">

            <section id="work-orders-tab" class="tab-content active mx-auto max-w-7xl">
                <div id="major-overhaul-alert" class="hidden mb-4 p-4 text-sm rounded-lg border border-yellow-400 bg-yellow-100 text-yellow-700 font-semibold">
                    </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-blue-900 mb-4">Work Orders</h2>
                    <div class="flex justify-end space-x-2 mb-4">
                        <button onclick="printWorkOrdersReport()" class="bg-gray-600 text-white px-4 py-2 rounded-lg action-button"><i class="fas fa-print mr-2"></i>Print All Work Orders</button>
                        <button onclick="openModal('addWorkOrderModal')" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button"><i class="fas fa-plus-circle mr-2"></i>New Work Order</button>
                    </div>
                    <div id="work-order-message" class="hidden mb-4 p-4 text-sm rounded-lg"></div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-200 text-left text-gray-600 uppercase text-sm">
                                    <th class="px-4 py-2 border-b-2 border-gray-300">ID</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Equipment</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Description</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Assigned To</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Status</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Approving Officer</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="work-order-table-body" class="divide-y divide-gray-200">
                                </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="equipment-tab" class="tab-content mx-auto max-w-7xl">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-blue-900 mb-4">Equipment Management</h2>
                    <div class="flex justify-end mb-4">
                        <button onclick="openModal('addEquipmentModal')" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button"><i class="fas fa-plus-circle mr-2"></i>Add Equipment</button>
                    </div>
                    <div id="equipment-message" class="hidden mb-4 p-4 text-sm rounded-lg"></div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-200 text-left text-gray-600 uppercase text-sm">
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Name</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Running Hours</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Last Maintenance</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Next Overhaul</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="equipment-table-body" class="divide-y divide-gray-200">
                                </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="inventory-tab" class="tab-content mx-auto max-w-7xl">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-blue-900 mb-4">Stock Inventory</h2>
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-2 md:space-y-0">
                        <div class="flex space-x-2 w-full md:w-auto">
                            <button onclick="openModal('addInventoryModal')" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button"><i class="fas fa-plus-circle mr-2"></i>Add Item</button>
                            <button onclick="printInventoryReport()" class="bg-gray-600 text-white px-4 py-2 rounded-lg action-button"><i class="fas fa-print mr-2"></i>Print Report</button>
                            <button onclick="openModal('printMajorOverhaulModal')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg action-button"><i class="fas fa-print mr-2"></i>Print Major Overhaul Parts</button>
                        </div>
                        <div class="flex items-center w-full md:w-auto">
                            <input type="text" id="inventory-search" placeholder="Search by item name or part number..." class="w-full md:w-64 p-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button onclick="searchInventory()" class="bg-blue-900 text-white px-4 py-2 rounded-r-lg action-button"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    <div class="mt-4 p-4 border border-gray-300 rounded-lg bg-gray-50 flex flex-col md:flex-row items-center justify-between space-y-2 md:space-y-0 md:space-x-4">
                        <div class="flex-grow w-full md:w-auto">
                            <label for="excel-file-upload" class="block font-semibold mb-1">Upload Spare Parts from Excel:</label>
                            <input type="file" id="excel-file-upload" accept=".xlsx, .xls" class="w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100">
                        </div>
                        <div class="flex space-x-2 w-full md:w-auto">
                            <select id="upload-mode" class="p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="add">Add to stock</option>
                                <option value="replace">Replace stock</option>
                            </select>
                            <button onclick="handleExcelUpload()" class="bg-green-600 text-white px-4 py-2 rounded-lg action-button">
                                <i class="fas fa-upload mr-2"></i>Process Upload
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>Ensure your Excel file has columns named 'name', 'quantity', and 'sparePartNo'.
                    </p>
                    <div id="inventory-message" class="hidden mb-4 p-4 text-sm rounded-lg"></div>
                    <div class="overflow-x-auto mt-4">
                        <table class="min-w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-200 text-left text-gray-600 uppercase text-sm">
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Item</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Quantity</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Spare Part No.</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Status</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inventory-table-body" class="divide-y divide-gray-200">
                                </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="staff-tab" class="tab-content mx-auto max-w-7xl">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-blue-900 mb-4">Staff Leave Management</h2>
                    <div class="flex justify-end mb-4">
                        <button onclick="openModal('addLeaveModal')" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button"><i class="fas fa-plus-circle mr-2"></i>Add Leave Record</button>
                    </div>
                    <div id="leave-message" class="hidden mb-4 p-4 text-sm rounded-lg"></div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-200 text-left text-gray-600 uppercase text-sm">
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Staff Name</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Start Date</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">End Date</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="leave-table-body" class="divide-y divide-gray-200">
                                </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="reports-tab" class="tab-content mx-auto max-w-7xl">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-blue-900 mb-4">Reports</h2>
                    <div class="flex items-center space-x-4 mb-4">
                        <label for="report-type" class="font-semibold">Select Report Type:</label>
                        <select id="report-type" class="flex-grow p-2 border border-gray-300 rounded-lg">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="annual">Annual</option>
                        </select>
                        <button onclick="generateReport()" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button"><i class="fas fa-file-alt mr-2"></i>Generate Report</button>
                        <button onclick="printReport()" class="bg-gray-600 text-white px-4 py-2 rounded-lg action-button"><i class="fas fa-print mr-2"></i>Print Report</button>
                    </div>
                    <div id="report-output" class="bg-gray-50 p-4 border border-gray-200 rounded-lg">
                        <p class="text-gray-500">Select a report type and click 'Generate Report' to see the data.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="messageModal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold" id="messageModalTitle">Message</h3>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeModal('messageModal')"><i class="fas fa-times"></i></button>
            </div>
            <div id="messageModalBody" class="mb-4"></div>
            <div class="flex justify-end space-x-2">
                <button id="messageModalConfirmBtn" class="hidden bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 action-button">Confirm</button>
                <button class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 action-button" onclick="closeModal('messageModal')">Close</button>
            </div>
        </div>
    </div>
    
    <div id="addWorkOrderModal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-2xl modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-blue-900">Add New Work Order</h3>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeModal('addWorkOrderModal')"><i class="fas fa-times"></i></button>
            </div>
            <form id="add-work-order-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="wo-equipment" class="block font-semibold mb-1">Equipment:</label>
                        <select id="wo-equipment" name="equipment" class="w-full p-2 border rounded-lg" required></select>
                    </div>
                    <div>
                        <label for="wo-assigned" class="block font-semibold mb-1">Assigned To:</label>
                        <input type="text" id="wo-assigned" name="assignedTo" placeholder="John Doe" class="w-full p-2 border rounded-lg" required>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label for="wo-description" class="block font-semibold mb-1">Description:</label>
                        <textarea id="wo-description" name="description" rows="3" placeholder="Describe the maintenance task..." class="w-full p-2 border rounded-lg" required></textarea>
                    </div>
                    <div>
                        <label for="wo-maintenance-type" class="block font-semibold mb-1">Maintenance Type:</label>
                        <select id="wo-maintenance-type" name="maintenanceType" class="w-full p-2 border rounded-lg" required>
                            <option value="">Select a type...</option>
                            <option value="major overhaul(E1)">Major Overhaul (E1)</option>
                            <option value="major overhaul(E10)">Major Overhaul (E10)</option>
                            <option value="major overhaul(E20)">Major Overhaul (E20)</option>
                            <option value="major overhaul(E40)">Major Overhaul (E40)</option>
                            <option value="major overhaul(E50)">Major Overhaul (E50)</option>
                            <option value="major overhaul(E60)">Major Overhaul (E60)</option>
                            <option value="major overhaul(E70)">Major Overhaul (E70)</option>
                            <option value="Corrective Maintenance">Corrective Maintenance</option>
                            <option value="Preventive Maintenance">Preventive Maintenance</option>
                            <option value="Breakdown">Breakdown</option>
                        </select>
                    </div>
                    <div>
                        <label for="wo-approving-officer" class="block font-semibold mb-1">Approving Officer:</label>
                        <input type="text" id="wo-approving-officer" name="approvingOfficer" placeholder="Jane Doe" class="w-full p-2 border rounded-lg" required>
                    </div>
                </div>
                <div id="wo-items-container" class="mt-4">
                    <h4 class="font-semibold mb-2">Items to be used:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="wo-item-list">
                        </div>
                </div>
                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg action-button" onclick="closeModal('addWorkOrderModal')">Cancel</button>
                    <button type="submit" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button">Create Work Order</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="addEquipmentModal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-blue-900">Add New Equipment</h3>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeModal('addEquipmentModal')"><i class="fas fa-times"></i></button>
            </div>
            <form id="add-equipment-form">
                <div class="mb-4">
                    <label for="equipment-name" class="block font-semibold mb-1">Equipment Name:</label>
                    <input type="text" id="equipment-name" name="name" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="equipment-hours" class="block font-semibold mb-1">Running Hours:</label>
                    <input type="number" id="equipment-hours" name="runningHours" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="equipment-last-maintenance" class="block font-semibold mb-1">Last Maintenance Date:</label>
                    <input type="date" id="equipment-last-maintenance" name="lastMaintenance" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="equipment-next-overhaul" class="block font-semibold mb-1">Next Major Overhaul Date:</label>
                    <input type="date" id="equipment-next-overhaul" name="nextMajorOverhaul" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg action-button" onclick="closeModal('addEquipmentModal')">Cancel</button>
                    <button type="submit" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button">Add Equipment</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addInventoryModal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-blue-900">Add New Inventory Item</h3>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeModal('addInventoryModal')"><i class="fas fa-times"></i></button>
            </div>
            <form id="add-inventory-form">
                <div class="mb-4">
                    <label for="inventory-name" class="block font-semibold mb-1">Item Name:</label>
                    <input type="text" id="inventory-name" name="name" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="inventory-quantity" class="block font-semibold mb-1">Quantity:</label>
                    <input type="number" id="inventory-quantity" name="quantity" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="inventory-spare-part-no" class="block font-semibold mb-1">Spare Part No.:</label>
                    <input type="text" id="inventory-spare-part-no" name="sparePartNo" class="w-full p-2 border rounded-lg">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg action-button" onclick="closeModal('addInventoryModal')">Cancel</button>
                    <button type="submit" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addLeaveModal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-blue-900">Add Staff Leave Record</h3>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeModal('addLeaveModal')"><i class="fas fa-times"></i></button>
            </div>
            <form id="add-leave-form">
                <div class="mb-4">
                    <label for="leave-name" class="block font-semibold mb-1">Staff Name:</label>
                    <input type="text" id="leave-name" name="staffName" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="leave-start" class="block font-semibold mb-1">Start Date:</label>
                    <input type="date" id="leave-start" name="startDate" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label for="leave-end" class="block font-semibold mb-1">End Date:</label>
                    <input type="date" id="leave-end" name="endDate" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg action-button" onclick="closeModal('addLeaveModal')">Cancel</button>
                    <button type="submit" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button">Add Record</button>
                </div>
            </form>
        </div>
    </div>

    <div id="printMajorOverhaulModal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-2xl modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-blue-900">Print Major Overhaul Parts</h3>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeModal('printMajorOverhaulModal')"><i class="fas fa-times"></i></button>
            </div>
            <p class="text-gray-700 mb-4">Select a Major Overhaul Work Order to print the spare parts list.</p>
            <div class="mb-4">
                <label for="major-overhaul-wo-select" class="block font-semibold mb-1">Work Order ID:</label>
                <select id="major-overhaul-wo-select" class="w-full p-2 border rounded-lg"></select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg action-button" onclick="closeModal('printMajorOverhaulModal')">Cancel</button>
                <button type="button" class="bg-blue-900 text-white px-4 py-2 rounded-lg action-button" onclick="printMajorOverhaulReport()">Print Report</button>
            </div>
        </div>
    </div>
    
    <script>
        const showMessage = (message, type = 'info', targetId = 'work-order-message') => {
            const targetEl = document.getElementById(targetId);
            const classes = {
                'success': 'bg-green-100 border-green-400 text-green-700',
                'info': 'bg-blue-100 border-blue-400 text-blue-700',
                'warning': 'bg-yellow-100 border-yellow-400 text-yellow-700',
                'error': 'bg-red-100 border-red-400 text-red-700'
            };
            targetEl.innerHTML = message;
            targetEl.className = `p-4 mb-4 rounded-lg border ${classes[type]}`;
            targetEl.classList.remove('hidden');
            setTimeout(() => targetEl.classList.add('hidden'), 5000);
        };

        const openModal = (modalId) => {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            if (modalId === 'addWorkOrderModal') {
                populateWorkOrderItems();
            } else if (modalId === 'printMajorOverhaulModal') {
                populateMajorOverhaulWorkOrders();
            }
        };

        const closeModal = (modalId) => {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
        };

        const showView = (tabId) => {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
        };

        const setActiveTab = (buttonId, tabId) => {
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            document.getElementById(buttonId).classList.add('active');
            showView(tabId);
        };

        // --- Supabase Initialization ---
        const SUPABASE_URL = 'https://snzzhaqxmvhpryqoocoa.supabase.co';
        const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNuenpoYXF4bXZocHJ5cW9vY29hIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTY5ODEyOTYsImV4cCI6MjA3MjU1NzI5Nn0.vEX_lXt2OrbjovBtCRx60jQ1Msep2mkeR_7A3TfElvQ';
        const supabase = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
        
        let workOrders = [];
        let equipment = [];
        let inventory = [];
        let staffLeave = [];

        // --- Auth Functions ---
        const login = async () => {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            const { data, error } = await supabase.auth.signInWithPassword({
                email: username,
                password: password
            });

            if (error) {
                document.getElementById('login-error').classList.remove('hidden');
            } else {
                document.getElementById('login-container').classList.add('hidden');
                document.getElementById('app-container').classList.remove('hidden');
                initializeApp();
            }
        };

        const logout = async () => {
            const { error } = await supabase.auth.signOut();
            document.getElementById('login-container').classList.remove('hidden');
            document.getElementById('app-container').classList.add('hidden');
            document.getElementById('login-form').reset();
            document.getElementById('login-error').classList.add('hidden');
        };

        // --- Data Fetching and Rendering Functions ---
        const fetchAndRenderWorkOrders = async () => {
            const { data, error } = await supabase.from('work_orders').select('*');
            if (error) {
                console.error('Error fetching work orders:', error.message);
                return;
            }
            workOrders = data;
            const tbody = document.getElementById('work-order-table-body');
            tbody.innerHTML = '';
            workOrders.forEach(order => {
                const row = document.createElement('tr');
                row.classList.add('hover:bg-gray-50');
                const statusClass = order.status === 'Completed' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800';
                row.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap">${order.id}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${order.equipment}</td>
                    <td class="px-4 py-2">${order.description}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${order.assignedTo}</td>
                    <td class="px-4 py-2 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${order.status}</span></td>
                    <td class="px-4 py-2 whitespace-nowrap">${order.approvingOfficer}</td>
                    <td class="px-4 py-2 whitespace-nowrap flex space-x-2">
                        <button onclick="printIndividualWorkOrder('${order.id}')" class="text-gray-600 hover:text-gray-900" title="Print Work Order"><i class="fas fa-print"></i></button>
                        <button onclick="completeWorkOrder('${order.id}')" class="text-green-600 hover:text-green-900" title="Mark as Complete"><i class="fas fa-check-circle"></i></button>
                        <button onclick="editWorkOrder('${order.id}')" class="text-blue-600 hover:text-blue-900" title="Edit"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteWorkOrder('${order.id}')" class="text-red-600 hover:text-red-900" title="Delete"><i class="fas fa-trash-alt"></i></button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        };

        const fetchAndRenderEquipment = async () => {
            const { data, error } = await supabase.from('equipment').select('*');
            if (error) {
                console.error('Error fetching equipment:', error.message);
                return;
            }
            equipment = data;
            const tbody = document.getElementById('equipment-table-body');
            tbody.innerHTML = '';
            equipment.forEach(item => {
                const row = document.createElement('tr');
                row.classList.add('hover:bg-gray-50');
                row.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap">${item.name}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${item.runningHours} hrs</td>
                    <td class="px-4 py-2 whitespace-nowrap">${item.lastMaintenance}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${item.nextMajorOverhaul}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <button onclick="editEquipment('${item.id}')" class="text-blue-600 hover:text-blue-900 mr-2"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteEquipment('${item.id}')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        };

        const fetchAndRenderInventory = async (searchTerm = '') => {
            const query = supabase.from('inventory').select('*');
            if (searchTerm) {
                query.or(`name.ilike.%${searchTerm}%,sparePartNo.ilike.%${searchTerm}%`);
            }
            const { data, error } = await query;
            if (error) {
                console.error('Error fetching inventory:', error.message);
                return;
            }
            inventory = data;
            const tbody = document.getElementById('inventory-table-body');
            tbody.innerHTML = '';
            if (inventory.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-2 text-center text-gray-500">No items found.</td></tr>`;
                return;
            }
            inventory.forEach(item => {
                const row = document.createElement('tr');
                row.classList.add('hover:bg-gray-50');
                const reorderStatus = item.quantity <= item.initialQuantity / 2;
                const statusText = reorderStatus ? `<span class="bg-red-200 text-red-800 px-2 inline-flex text-xs leading-5 font-semibold rounded-full">Re-order</span>` : `<span class="bg-green-200 text-green-800 px-2 inline-flex text-xs leading-5 font-semibold rounded-full">In Stock</span>`;
                row.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap">${item.name}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${item.quantity}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${item.sparePartNo || 'N/A'}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${statusText}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <button onclick="editInventory('${item.id}')" class="text-blue-600 hover:text-blue-900 mr-2"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteInventory('${item.id}')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        };

        const fetchAndRenderStaffLeave = async () => {
            const { data, error } = await supabase.from('staff_leave').select('*');
            if (error) {
                console.error('Error fetching staff leave:', error.message);
                return;
            }
            staffLeave = data;
            const tbody = document.getElementById('leave-table-body');
            tbody.innerHTML = '';
            staffLeave.forEach(item => {
                const row = document.createElement('tr');
                row.classList.add('hover:bg-gray-50');
                row.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap">${item.name}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${item.startDate}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${item.endDate}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <button onclick="editLeave('${item.id}')" class="text-blue-600 hover:text-blue-900 mr-2"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteLeave('${item.id}')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        };

        // --- Form Population Functions ---
        const populateWorkOrderItems = async () => {
            const { data, error } = await supabase.from('inventory').select('*');
            if (error) {
                console.error('Error fetching inventory for work order:', error.message);
                return;
            }
            const woItemList = document.getElementById('wo-item-list');
            woItemList.innerHTML = '';
            data.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.classList.add('flex', 'items-center', 'space-x-2');
                itemDiv.innerHTML = `
                    <label for="item-${item.id}" class="text-gray-700">${item.name} (${item.quantity} in stock)</label>
                    <input type="number" id="item-${item.id}" data-item-id="${item.id}" min="0" max="${item.quantity}" value="0" class="w-24 p-2 border rounded-lg">
                `;
                woItemList.appendChild(itemDiv);
            });
        };

        const populateMajorOverhaulWorkOrders = async () => {
            const { data, error } = await supabase.from('work_orders').select('*').like('maintenanceType', 'major overhaul%');
            if (error) {
                console.error('Error fetching major overhaul work orders:', error.message);
                return;
            }
            const select = document.getElementById('major-overhaul-wo-select');
            select.innerHTML = '';
            if (data.length === 0) {
                const option = document.createElement('option');
                option.text = "No major overhaul work orders found.";
                option.disabled = true;
                select.add(option);
            } else {
                data.forEach(wo => {
                    const option = document.createElement('option');
                    option.value = wo.id;
                    option.text = `WO-${wo.id}: ${wo.equipment} (${wo.maintenanceType})`;
                    select.add(option);
                });
            }
        };

        // --- Event Listeners and Handlers ---
        document.getElementById('login-form').addEventListener('submit', (e) => {
            e.preventDefault();
            login();
        });

        document.getElementById('add-work-order-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const equipmentName = document.getElementById('wo-equipment').value;
            const assignedTo = document.getElementById('wo-assigned').value;
            const description =