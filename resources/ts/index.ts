import jQuery from "jquery";
import notus from "notus";

jQuery(function () {
  notus().send({
    message: "Lorem ipsum message",
    alertType: "failure",
    animate: true,
    closable: true,
  });
});
