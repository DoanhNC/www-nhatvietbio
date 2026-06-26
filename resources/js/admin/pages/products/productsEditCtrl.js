adminApp.controller("ProductsEditCtrl", [
    "$scope",
    "$http",
    "BASE_API",
    "$toastr",
    "$q",
    function ($scope, $http, BASE_API, $toastr, $q) {
        // refs
        $scope.units = pageData.units || [];
        $scope.brands = pageData.brands || [];
        $scope.categories = pageData.categories || [];

        // model
        $scope.model = pageData.data || {};
        $scope.model.unit_id = $scope.model.unit_id || "";
        $scope.model.brand_id = $scope.model.brand_id || "";
        $scope.model.category_id = $scope.model.category_id || "";
        $scope.isEdit = true;
        $scope.saving = false;

        const slugify = (s) =>
            (s || "")
                .toString()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, "-")
                .replace(/(^-|-$)+/g, "");
        $scope.autoSlug = () => {
            if (!$scope.model.slug) {
                $scope.model.slug = slugify(
                    $scope.model.name || $scope.model.title
                );
            }
        };

        // images
        $scope.images = [];
        $scope.files = [];
        $scope.uploading = false;
        $scope.loadImages = () =>
            $http
                .get(`${BASE_API}/products/${$scope.model.id}/images`)
                .then((r) => ($scope.images = r.data || []));
        $scope.uploadImages = () => {
            if (!$scope.files || !$scope.files.length) return;
            $scope.uploading = true;
            const fd = new FormData();
            for (let i = 0; i < $scope.files.length; i++)
                fd.append("images[]", $scope.files[i]);
            $http
                .post(`${BASE_API}/products/${$scope.model.id}/images`, fd, {
                    headers: { "Content-Type": undefined },
                    transformRequest: angular.identity,
                })
                .then(() => {
                    $toastr.show("Uploaded", "success");
                    $scope.files = [];
                    $scope.loadImages();
                })
                .catch(() => $toastr.show("Upload failed", "error"))
                .finally(() => ($scope.uploading = false));
        };
        $scope.setPrimary = (img) =>
            $http
                .post(
                    `${BASE_API}/products/${$scope.model.id}/images/${img.id}/primary`
                )
                .then(() => $scope.loadImages());
        $scope.deleteImage = (img) => {
            if (!confirm("Xoá ảnh này?")) return;
            $http
                .delete(
                    `${BASE_API}/products/${$scope.model.id}/images/${img.id}`
                )
                .then(() => $scope.loadImages());
        };

        // attributes (server only here; không cần staged vì đã có id)
        $scope.attributes = [];
        $scope.attrModel = {};
        $scope.attrIsEdit = false;
        $scope.attrSaving = false;
        const tryParse = (t, f) => {
            try {
                const v = JSON.parse(t || "[]");
                return Array.isArray(v) ? v : f;
            } catch (e) {
                return f;
            }
        };

        $scope.loadAttributes = () =>
            $http
                .get(`${BASE_API}/products/${$scope.model.id}/attributes`)
                .then((r) => ($scope.attributes = r.data || []));
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
            const req = $scope.attrIsEdit
                ? $http.put(
                      `${BASE_API}/products/${$scope.model.id}/attributes/${$scope.attrModel.id}`,
                      payload
                  )
                : $http.post(
                      `${BASE_API}/products/${$scope.model.id}/attributes`,
                      payload
                  );
            req.then(() => {
                $("#attrModal").modal("hide");
                $scope.loadAttributes();
                $toastr.show("Đã lưu thuộc tính", "success");
            })
                .catch((e) =>
                    $toastr.show(e?.data?.message || "Lưu thất bại", "error")
                )
                .finally(() => ($scope.attrSaving = false));
        };

        $scope.attrRemove = (a) => {
            if (!confirm("Xoá thuộc tính này?")) return;
            $http
                .delete(
                    `${BASE_API}/products/${$scope.model.id}/attributes/${a.id}`
                )
                .then(() => $scope.loadAttributes());
        };
        $scope.attrToggle = (a) =>
            $http
                .post(
                    `${BASE_API}/products/${$scope.model.id}/attributes/${a.id}/toggle`
                )
                .then(
                    (res) => (a.is_active = res.data?.is_active ?? a.is_active)
                );

        // save product
        $scope.save = () => {
            $scope.saving = true;
            const p = { ...$scope.model };
            if (p.unit_id === "") p.unit_id = null;
            if (p.brand_id === "") p.brand_id = null;
            if (p.category_id === "") p.category_id = null;
            $http
                .put(`${BASE_API}/products/${p.id}`, p)
                .then(() => {
                    $toastr.show("Đã cập nhật sản phẩm", "success");
                    window.location.href = pageData.listUrl;
                })
                .catch((e) =>
                    $toastr.show(
                        e?.data?.message || "Cập nhật thất bại",
                        "error"
                    )
                )
                .finally(() => ($scope.saving = false));
        };

        // init
        //$scope.loadRefs();
        $scope.loadImages();
        $scope.loadAttributes();
    },
]);
