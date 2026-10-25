document.addEventListener('DOMContentLoaded', function () {
    var menuToggle = document.getElementById('menuToggle');
    var sidebar = document.getElementById('sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    var departmentSelect = document.getElementById('department_id');
    var courseSelect = document.getElementById('course_id');

    if (departmentSelect && courseSelect) {
        var filterCourses = function () {
            var selectedDepartment = departmentSelect.value;

            Array.prototype.forEach.call(courseSelect.options, function (option) {
                if (option.value === '') {
                    return;
                }
                var matches = option.dataset.departmentId === selectedDepartment;
                option.hidden = !matches;
                option.disabled = !matches;
            });

            if (courseSelect.options[courseSelect.selectedIndex] && courseSelect.options[courseSelect.selectedIndex].disabled) {
                courseSelect.value = '';
            }
        };

        departmentSelect.addEventListener('change', filterCourses);
        filterCourses();
    }
});
