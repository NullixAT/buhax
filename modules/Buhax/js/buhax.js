/**
 * Buhax Module Class
 */
class Buhax {
  /**
   * Init late
   */
  static initLate () {
    $(document).on('click', '[data-flag-icon] button[data-action]', async function () {
      const action = $(this).attr('data-action')
      switch (action) {
        case 'invoice-pdf-download':
          FramelixModal.callPhpMethod($(this).attr('data-url'))
          break
      }
    })
  }
}

FramelixInit.late.push(Buhax.initLate)