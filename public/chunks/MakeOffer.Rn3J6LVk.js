import { _, b as h, d as t, t as u, s as f, v as m, o as g } from "../entry/index.R2o0HwGI.js";
const x = {
    data() {
      return { items: [], loading: !1, item: {}, ID: this.$route.params.id, price: "", quantity: 1 };
    },
    methods: {
      makeOffer() {
        let o = { product_id: this.ID, quantity: this.quantity, offer_price: this.price };
        this.$user.addToCart(o).then((e) => {
          var l;
          console.log(e), this.$router.push(`/app/messages/?user=${(l = e == null ? void 0 : e.cart) == null ? void 0 : l.user_id}`);
        });
      },
      getProduct() {
        (this.loading = !0),
          this.$products
            .getRecord(this.ID)
            .then((o) => {
              console.log("data from products list:", o), (this.item = o.data);
            })
            .finally(() => {
              this.loading = !1;
            });
      },
    },
    beforeMount() {
      this.getProduct();
    },
    computed: {
      user() {
        return this.$store.getters["auth/getUser"];
      },
    },
  },
  b = { class: "lg:page-bg md:page-bg lg:w-6/12 md:w-7/12 w-full mx-auto" },
  y = t("h4", { class: "font-semibold" }, "Make Offer", -1),
  k = t("hr", { class: "my-4" }, null, -1),
  v = { class: "mb-3" },
  O = { class: "flex gap-2" },
  q = ["src"],
  w = { class: "flex gap-[4px] flex-col" },
  D = { class: "font-semibold block" },
  M = { class: "block text-[13px] text-primary font-semibold" },
  U = { class: "flex flex-col gap-2" },
  A = t("label", { for: "", class: "text-[14px]" }, "Quantity", -1),
  B = t("label", { for: "", class: "text-[14px]" }, "Amount", -1),
  E = { class: "text-center mt-6" };
function I(o, e, l, V, s, r) {
  var n, a, c, d, p;
  return (
    g(),
    h("div", b, [
      y,
      k,
      t("div", v, [
        t("div", O, [
          t("img", { src: o.imgUrl + "product/" + ((n = s.item) == null ? void 0 : n.main_image), alt: "", role: "button", class: "h-[60px] w-[65px] object-contain rounded-lg" }, null, 8, q),
          t("div", w, [
            t("span", D, u((a = s.item) == null ? void 0 : a.name), 1),
            t(
              "span",
              M,
              u((c = s.item) != null && c.offer_price ? o.$currencyFormat((d = s.item) == null ? void 0 : d.offer_price) : o.$currencyFormat((p = s.item) == null ? void 0 : p.base_price)),
              1
            ),
          ]),
        ]),
      ]),
      t("div", U, [
        t("div", null, [
          A,
          f(t("input", { type: "text", "onUpdate:modelValue": e[0] || (e[0] = (i) => (s.quantity = i)), class: "input", placeholder: "Enter Offer Amount" }, null, 512), [[m, s.quantity]]),
        ]),
        t("div", null, [
          B,
          f(t("input", { type: "text", "onUpdate:modelValue": e[1] || (e[1] = (i) => (s.price = i)), class: "input", placeholder: "Enter Offer Amount" }, null, 512), [[m, s.price]]),
        ]),
      ]),
      t("div", E, [t("button", { onClick: e[2] || (e[2] = (...i) => r.makeOffer && r.makeOffer(...i)), class: "brand-btn brand-primary px-[30px] py-[10px]" }, " Make Offer ")]),
    ])
  );
}
const F = _(x, [["render", I]]);
export { F as default };
