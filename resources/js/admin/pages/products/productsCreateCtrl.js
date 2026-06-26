adminApp.controller("ProductsCreateCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    "$q",
    function ($scope, $http, BASE_API, $toastr, $q) {
        /* ========= Helpers reset ========= */
        function defaultModel() {
            return {
                name: "",
                title: "",
                slug: "",
                unit_id: "",
                brand_id: "",
                category_id: "",
                description: "",
            };
        }
        function resetForm() {
            $scope.model = defaultModel();
            $scope.isEdit = false;
            $scope.images = [];
            $scope.stagedImages = [];
            $scope.files = [];
            $scope.uploading = false;
            setTimeout(() => {
                const el = document.querySelector(
                    'input[ng-model="model.name"]'
                );
                if (el) el.focus();
            }, 0);
        }

        /* ========= Refs ========= */
        $scope.units = [];
        $scope.brands = [];
        $scope.categories = [];
        $scope.loadRefs = () => {
            $http
                .get(`${BASE_API}/units`, { params: { per_page: 1000 } })
                .then((r) => {
                    const d = r.data;
                    $scope.units = d.data || d;
                });
            $http
                .get(`${BASE_API}/brands`, { params: { per_page: 1000 } })
                .then((r) => {
                    const d = r.data;
                    $scope.brands = d.data || d;
                });
            $http
                .get(`${BASE_API}/product-types`, {
                    params: { per_page: 1000 },
                })
                .then((r) => {
                    const d = r.data;
                    $scope.categories = d.data || d;
                });
        };

        /* ========= State ========= */
        $scope.model = defaultModel();
        $scope.saving = false;
        $scope.isEdit = false;

        const slugify = (s) =>
            (s || "")
                .toString()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, "-")
                .replace(/(^-|-$)+/g, "");
        $scope.autoSlug = () => {
            if (!$scope.isEdit || !$scope.model.slug) {
                $scope.model.slug = slugify(
                    $scope.model.name || $scope.model.title
                );
            }
        };

        /* ========= Images ========= */
        $scope.images = []; // ảnh đã upload (sau khi có product_id)
        $scope.files = []; // input file tạm
        $scope.stagedImages = []; // { _key, file, url, is_primary }
        $scope.uploading = false;

        // Thêm các file đã chọn vào danh sách staged để preview
        $scope.addSelectedFiles = () => {
            if (!$scope.files || !$scope.files.length) return;
            Array.from($scope.files).forEach((f) => {
                $scope.stagedImages.push({
                    _key:
                        Date.now().toString() +
                        Math.random().toString(36).slice(2),
                    file: f,
                    url: URL.createObjectURL(f),
                    is_primary: false,
                });
            });
            // clear input file
            $scope.files = [];
        };

        $scope.toggleStagedPrimary = (s) => {
            // chỉ cho phép 1 ảnh chính trong staged
            $scope.stagedImages.forEach((x) => (x.is_primary = false));
            s.is_primary = true;
        };

        $scope.removeStaged = (s) => {
            // thu hồi blob URL để tránh leak
            try {
                URL.revokeObjectURL(s.url);
            } catch (e) {}
            $scope.stagedImages = $scope.stagedImages.filter(
                (x) => x._key !== s._key
            );
        };

        $scope.loadImages = () => {
            if (!$scope.model.id || $scope.isEdit) {
                $scope.images = [];
                return;
            }
            $http
                .get(`${BASE_API}/products/${$scope.model.id}/images`)
                .then((r) => ($scope.images = r.data || []));
        };

        // Upload ngay (nếu đã có product_id) các ảnh đang staged + files chưa add
        $scope.uploadImages = () => {
            if (!$scope.model.id) {
                $toastr.show(
                    "Hãy lưu sản phẩm trước khi upload ảnh",
                    "warning"
                );
                return;
            }
            // gom ảnh staged + files (nếu người dùng chưa bấm "Thêm vào danh sách")
            if ($scope.files && $scope.files.length) {
                Array.from($scope.files).forEach((f) => {
                    $scope.stagedImages.push({
                        _key: Date.now().toString() + Math.random(),
                        file: f,
                        url: URL.createObjectURL(f),
                        is_primary: false,
                    });
                });
                $scope.files = [];
            }
            if (!$scope.stagedImages.length) return;

            $scope.uploading = true;
            const fd = new FormData();
            $scope.stagedImages.forEach((s) => fd.append("images[]", s.file));

            $http
                .post(`${BASE_API}/products/${$scope.model.id}/images`, fd, {
                    headers: { "Content-Type": undefined },
                    transformRequest: angular.identity,
                })
                .then((res) => {
                    const created = res.data?.files || [];
                    $toastr.show("Upload ảnh thành công", "success");
                    // nếu có staged primary -> set primary cho ảnh tương ứng (theo index)
                    const idxPrimary = $scope.stagedImages.findIndex(
                        (x) => x.is_primary
                    );
                    if (idxPrimary >= 0 && created[idxPrimary]?.id) {
                        return $http.post(
                            `${BASE_API}/products/${$scope.model.id}/images/${created[idxPrimary].id}/primary`
                        );
                    }
                })
                .then(() => $scope.loadImages())
                .finally(() => {
                    // dọn staged
                    $scope.stagedImages.forEach((s) => {
                        try {
                            URL.revokeObjectURL(s.url);
                        } catch (e) {}
                    });
                    $scope.stagedImages = [];
                    $scope.uploading = false;
                });
        };

        /* ========= Attributes (staged như trước) ========= */
        $scope.attributes = [];
        $scope.attrModel = {};
        $scope.attrIsEdit = false;
        $scope.attrSaving = false;
        const makeTempId = () =>
            -Math.floor(Date.now() + Math.random() * 100000);
        const tryParse = (t, f) => {
            try {
                const v = JSON.parse(t || "[]");
                return Array.isArray(v) ? v : f;
            } catch (e) {
                return f;
            }
        };
        $scope.loadAttributes = () => {
            if (!$scope.model.id || $scope.isEdit) {
                $scope.attributes = [];
                return;
            }
            $http
                .get(`${BASE_API}/products/${$scope.model.id}/attributes`)
                .then((r) => {
                    const server = r.data || [];
                    const staged = $scope.attributes.filter((x) => x._staged);
                    $scope.attributes = [...server, ...staged];
                });
        };
        $scope.openAttrCreate = () => {
            $scope.attrIsEdit = false;
            $scope.attrModel = {
                name: "",
                price_old: 0,
                price_new: 0,
                quantity: 0,
                is_active: true,
                image_paths_text: "[]",
            };
            $("#attrModal").modal("show");
        };
        $scope.openAttrEdit = (a) => {
            $scope.attrIsEdit = true;
            $scope.attrModel = {
                id: a.id,
                _staged: !!a._staged,
                _tempId: a._tempId,
                name: a.name,
                price_old: a.price_old,
                price_new: a.price_new,
                quantity: a.quantity,
                is_active: !!a.is_active,
                image_paths_text: JSON.stringify(a.image_paths || []),
            };
            $("#attrModal").modal("show");
        };
        function attrPayload(m) {
            return {
                name: m.name,
                price_old: parseInt(m.price_old || 0, 10),
                price_new: parseInt(m.price_new || 0, 10),
                quantity: parseFloat(m.quantity || 0),
                is_active: m.is_active ? 1 : 0,
                image_paths: m.image_paths_text,
            };
        }
        $scope.attrSave = () => {
            $scope.attrSaving = true;
            const payload = attrPayload($scope.attrModel);
            if ($scope.attrIsEdit && $scope.attrModel._staged) {
                const i = $scope.attributes.findIndex(
                    (x) => x._staged && x._tempId === $scope.attrModel._tempId
                );
                if (i >= 0) {
                    $scope.attributes[i] = {
                        ...$scope.attributes[i],
                        name: payload.name,
                        price_old: payload.price_old,
                        price_new: payload.price_new,
                        quantity: payload.quantity,
                        is_active: payload.is_active,
                        image_paths: tryParse(
                            $scope.attrModel.image_paths_text,
                            []
                        ),
                    };
                }
                $("#attrModal").modal("hide");
                $scope.attrSaving = false;
                return;
            }
            if ($scope.model.id) {
                $http
                    .post(
                        `${BASE_API}/products/${$scope.model.id}/attributes`,
                        payload
                    )
                    .then(() => {
                        $("#attrModal").modal("hide");
                        $scope.loadAttributes();
                        $toastr.show("Đã lưu thuộc tính", "success");
                    })
                    .catch((e) =>
                        $toastr.show(
                            e?.data?.message || "Lưu thất bại",
                            "error"
                        )
                    )
                    .finally(() => ($scope.attrSaving = false));
                return;
            }
            $scope.attributes.push({
                _staged: true,
                _tempId: makeTempId(),
                id: null,
                name: payload.name,
                price_old: payload.price_old,
                price_new: payload.price_new,
                quantity: payload.quantity,
                is_active: payload.is_active,
                image_paths: tryParse($scope.attrModel.image_paths_text, []),
            });
            $("#attrModal").modal("hide");
            $scope.attrSaving = false;
        };
        $scope.attrRemove = (a) => {
            if (a._staged) {
                $scope.attributes = $scope.attributes.filter(
                    (x) => !(x._staged && x._tempId === a._tempId)
                );
                return;
            }
            if (!confirm("Xoá thuộc tính này?")) return;
            $http
                .delete(
                    `${BASE_API}/products/${$scope.model.id}/attributes/${a.id}`
                )
                .then(() => $scope.loadAttributes());
        };
        $scope.attrToggle = (a) => {
            if (a._staged) {
                a.is_active = a.is_active ? 0 : 1;
                return;
            }
            $http
                .post(
                    `${BASE_API}/products/${$scope.model.id}/attributes/${a.id}/toggle`
                )
                .then(
                    (res) => (a.is_active = res.data?.is_active ?? a.is_active)
                );
        };

        function flushStagedAttributes(productId) {
            const staged = $scope.attributes.filter((a) => a._staged);
            if (!staged.length) return $q.resolve();
            const tasks = staged.map((a) =>
                $http.post(`${BASE_API}/products/${productId}/attributes`, {
                    name: a.name,
                    price_old: a.price_old,
                    price_new: a.price_new,
                    quantity: a.quantity,
                    is_active: a.is_active,
                    image_paths: JSON.stringify(a.image_paths || []),
                })
            );
            return $q.all(tasks).then(() => $scope.loadAttributes());
        }

        /* ========= SAVE ========= */
        $scope.save = () => {
            $scope.saving = true;
            const p = { ...$scope.model };
            if (p.unit_id === "") p.unit_id = null;
            if (p.brand_id === "") p.brand_id = null;
            if (p.category_id === "") p.category_id = null;

            $http
                .post(`${BASE_API}/products`, p)
                .then((res) => {
                    $scope.isEdit = true;
                    $scope.model.id = res.data?.id;

                    // 1) flush thuộc tính staged
                    return (
                        flushStagedAttributes($scope.model.id)
                            // 2) upload ảnh staged (nếu có)
                            .then(() => uploadStagedImagesAuto())
                    );
                })
                .then(() => {
                    $toastr.show("Đã lưu sản phẩm", "success");
                    resetForm(); // reset toàn bộ dữ liệu nhập
                })
                .catch((e) =>
                    $toastr.show(e?.data?.message || "Lưu thất bại", "error")
                )
                .finally(() => ($scope.saving = false));
        };

        // Upload ảnh staged sau khi lưu sản phẩm lần đầu
        function uploadStagedImagesAuto() {
            if (!$scope.stagedImages.length) return $q.resolve();
            $scope.uploading = true;
            const fd = new FormData();
            $scope.stagedImages.forEach((s) => fd.append("images[]", s.file));
            let idxPrimary = $scope.stagedImages.findIndex((x) => x.is_primary);

            return $http
                .post(`${BASE_API}/products/${$scope.model.id}/images`, fd, {
                    headers: { "Content-Type": undefined },
                    transformRequest: angular.identity,
                })
                .then((res) => {
                    const created = res.data?.files || [];
                    if (idxPrimary >= 0 && created[idxPrimary]?.id) {
                        return $http.post(
                            `${BASE_API}/products/${$scope.model.id}/images/${created[idxPrimary].id}/primary`
                        );
                    }
                })
                .finally(() => {
                    $scope.stagedImages.forEach((s) => {
                        try {
                            URL.revokeObjectURL(s.url);
                        } catch (e) {}
                    });
                    $scope.stagedImages = [];
                    $scope.uploading = false;
                    return $scope.loadImages();
                });
        }

        // init
        $scope.loadRefs();
    },
]);
