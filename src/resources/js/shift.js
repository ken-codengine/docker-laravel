import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import { Modal } from 'bootstrap';
import * as holiday_jp from '@holiday-jp/holiday_jp';

var createModalEl = document.getElementById('createModal')
var deleteModalEl = document.getElementById('deleteModal')
var confirmModalEl = document.getElementById('confirmModal')
var cautionModalEl = document.getElementById('cautionModal')
// var createModal = new Modal(createModalEl, {});
// var deleteModal = new Modal(deleteModalEl, {});
// var confirmModal = new Modal(confirmModalEl, {});
// var cautionModal = new Modal(cautionModalEl, {});
// document.addEventListener('DOMContentLoaded', function () {
//   createModal.show();
// var editModal = new Modal(editModalEl,{});
// });
var calendarEl = document.getElementById('shift');

// モーダルを表示する関数
function showModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('hidden');
    modal.classList.add('opacity-0'); // 初期状態を透明に設定
    setTimeout(() => {
      modal.classList.remove('opacity-0');
      modal.classList.add('opacity-100'); // 透明度を100%に設定
    }, 10); // 少し遅延させてアニメーションをトリガー

    // 背景を固定
    document.body.style.overflow = 'hidden';
  }
}
// モーダルを非表示にする関数
function hideModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0'); // 透明度を0%に設定
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('opacity-0'); // 初期状態に戻す
    }, 1000); // アニメーションの時間に合わせて遅延させる
  }
}

const modalButtons = document.querySelectorAll('[data-modal-dismiss]');
modalButtons.forEach(button => {
  button.addEventListener('click', function () {
    const modalId = this.getAttribute('data-modal-dismiss');
    hideModal(modalId);
  });
});

let calendar = new Calendar(calendarEl, {
  //表示テーマ
  themeSystem: 'bootstrap',
  contentHeight: '75vh',
  plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    // right: 'dayGridMonth,timeGridWeek,listWeek'
    right: 'dayGridMonth,listMonth'
  },
  // スマホでタップしたとき即反応
  selectLongPressDelay: 0,
  locale: 'ja',
  buttonText: {
    today: '今月',
    month: '月',
    list: 'リスト'
  },
  // all-day表示を終日にする
  allDayText: '終日',
  // デフォルトの6週間表示を自動調整
  fixedWeekCount: false,

  //祝日に赤spanタグを挿入
  dayCellContent: function (arg) {
    // console.log(arg);
    const date = new Date();
    date.setFullYear(
      arg.date.getFullYear(),
      arg.date.getMonth(),
      arg.date.getDate()
    );
    const holiday = holiday_jp.between(new Date(date), new Date(date));
    let hol_tag = document.createElement('span')
    if (holiday[0]) {
      hol_tag.innerHTML = `${arg.date.getDate()}`
      hol_tag.className = 'fc-day-hol';

      let arrayOfDomNodes = [hol_tag]
      return { domNodes: arrayOfDomNodes }
    } else {
      //日本語化の日表示を外す
      arg.dayNumberText = arg.dayNumberText.replace('日', '');
      return arg.dayNumberText;
    }
  },

  //　カレンダー読み込み時に祝日に赤spanタグを挿入
  eventDidMount: function (mountArg) {
    const el = mountArg.el
    if (mountArg.view.type == "listMonth") {
      const date = new Date();
      date.setFullYear(
        mountArg.event.start.getFullYear(),
        mountArg.event.start.getMonth(),
        mountArg.event.start.getDate()
      );
      const holiday = holiday_jp.between(new Date(date), new Date(date));
      if (holiday[0]) {
        console.log(holiday);
        el.previousSibling.classList.add('fc-day-hol');
      }
    };
  },

  // カレンダー読み込み時イベントの取得
  events: function (info, successCallback, failureCallback) {
    const startDate = new Date();
    const endDate = new Date();
    startDate.setFullYear(
      info.start.getFullYear(),
      info.start.getMonth(),
      info.start.getDate()
    );
    endDate.setFullYear(
      info.end.getFullYear(),
      info.end.getMonth(),
      info.end.getDate()
    );

    // 表示中カレンダーの月初と終わりの範囲にあるイベントを取得
    axios
      .post("/dashboard/show", {
        start_date: info.start.valueOf(),
        end_date: info.end.valueOf(),
      })
      .then((response) => {
        // 一旦全てのイベントを削除
        calendar.removeAllEvents();
        // 取得したイベントをカレンダーに読み込み
        successCallback(response.data);
      })
      .catch(() => {
        // バリデーションエラーなど
        alert("取得に失敗しました");
      });
  },

  // イベントの新規登録
  selectable: true,
  // 表示中の月の1or5周目に前or次月の日付以外を選択可能範囲に指定
  selectAllow: function (selectInfo) {
    // 表示中の月の月初・月末を取得
    const view = calendar.view;
    const startOfMonth = view.currentStart;
    const endOfMonth = view.currentEnd;

    // クリックした日の開始日と終了日に当たる値を取得
    const start = selectInfo.start;
    const end = selectInfo.end;

    // クリックした日が表示中の月に収まっているかチェック
    if (start < startOfMonth || end > endOfMonth) {
      return false; // 選択不可
    }

    // ドラッグによる複数日にまたがる選択を不可にする
    // 開始日と終了日の値が24時間以内であれば選択を許可
    const timeDifference = end.getTime() - start.getTime();
    const oneDayInMilliseconds = 24 * 60 * 60 * 1000;
    if (timeDifference <= oneDayInMilliseconds) {
      return true; // 選択可
    }

    return false; // それ以外は選択不可
  },

  select: function (info) {
    // 表示中のカレンダー月がロックされているか判定し登録or警告モーダル表示
    axios
      .post("/dashboard/lock_month/show", {
        //fulucalenderのinfo.view.titleは表示中の月(2024年⚪︎月)
        title: info.view.title
      })
      .then((response) => {
        var titleExists = response.data.titleExists;

        // ロックされた月か判定
        if (titleExists === true) {
          // 警告モーダルを表示する
          showModal('cautionModal');
          document.getElementById('caution-text').innerText = "";
        } else {
          // チェックボックスを全て未選択にしておく
          let checkboxes = document.querySelectorAll('.form-check-input');
          checkboxes.forEach(checkbox => checkbox.checked = false);
          // モーダルの日付をクリックした日に設定にしておく
          document.getElementById('create_date').value = info.startStr;
          // 新規登録モーダルを表示する
          // createModal.show();
          showModal('createModal');

          // 保存ボタンによる送信を行う関数
          const saveOnClick = async () => {
            // 保存前にロックされた月を取得して判定
            const response = await axios.post("/dashboard/lock_month/show", {
              // info.view.titleで表示中の月(2024年⚪︎月)を送信
              title: info.view.title
            });
            // titleExistsに返ってきたboolean値を格納
            var titleExists = response.data.titleExists;

            //ロックされていた場合
            if (titleExists === true) {
              // 警告モーダルを表示
              showModal('cautionModal');
              //　登録済みの予定を警告文に表示
              document.getElementById('caution-text').innerText = info.event.startStr + " " + info.event.title;

              // ロックされていない場合
            } else {
              // クリックした日を日付型として取得
              const date = document.getElementById('create_date').valueAsDate

              // 各チェックボックスの状態を取得し、配列に格納
              let checkboxStates = Array.from(checkboxes).map(checkbox => checkbox.checked ? '⚪︎' : '×').join('/');
              // Laravelのaxiosから登録処理の呼び出し
              axios
                .post("/dashboard/store", {
                  // start_date: info.start.valueOf(),
                  // end_date: info.end.valueOf(),
                  date: date,
                  checkboxStates: checkboxStates  // チェックボックスの状態を送信します
                })
                .then((response) => {
                  // カレンダーに読み込み
                  calendar.addEvent({
                    // PHP側から受け取ったevent_idをeventObjectのidにセット
                    id: response.data.id,
                    title: response.data.title,
                    color: response.data.color,
                    start: response.data.start
                  });
                  //renderevent();はv3まで
                  calendar.refetchEvents();
                  // console.log(response);
                })
                .catch(() => {
                  // バリデーションエラーなど
                  alert("取得に失敗しました");
                });
            }
          };

          //保存ボタンによる送信、その後イベントの解除
          const close = document.getElementById('store-btn');
          close.addEventListener('click', saveOnClick)
          createModalEl.addEventListener('transitionend', () => {
            if (createModalEl.classList.contains('hidden')) {
              close.removeEventListener('click', saveOnClick);
            }
          }, { once: true });
        }
      });
  },

  // 登録済みイベントの編集・削除
  eventClick: function (info) {
    // イベントクリック時の月初めの値(info.view.currentStart)とDB(lock_month)群を比較
    axios // lock_month取得処理の呼び出し
      .post("/dashboard/lock_month/show", {
        title: info.view.title
      })
      .then((response) => {
        var titleExists = response.data.titleExists;

        // チェックボックスのDOM要素を取得します
        let checkboxes = document.querySelectorAll('.form-check-input');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        // document.getElementById('createModal_description').innerText = info.startStr;
        document.getElementById('create_date').value = info.startStr;

        if (titleExists === true) { // データベースの月が1から始まる場合、+1を忘れずに
          showModal('cautionModal');
          document.getElementById('caution-text').innerText = info.event.startStr + " " + info.event.title;
        } else {
          deleteModal.show();
          document.getElementById('delete-text').innerText = info.event.startStr + " " + info.event.title;
          const close = document.getElementById('delete-btn');

          const deleteOnClick = async () => {
            // 再度titleExistsを取得して判定
            const response = await axios.post("/dashboard/lock_month/show", {
              title: info.view.title
            });
            var titleExists = response.data.titleExists;

            if (titleExists === true) {
              showModal('cautionModal');
              document.getElementById('caution-text').innerText = info.event.startStr + " " + info.event.title;
            } else {
              axios
                .post("/dashboard/delete", {
                  id: info.event.id
                })
                .then((response) => {
                  var event = calendar.getEventById(response.data.id)
                  event.remove();
                  // //renderevent();はv3まで
                  // calendar.refetchEvents();
                })
                .catch(() => {
                  // バリデーションエラーなど
                  alert("取得に失敗しました");
                });
            }
          };

          //保存ボタンによる送信、その後イベントの解除
          close.addEventListener('click', deleteOnClick)
          deleteModalEl.addEventListener('hidden.bs.modal', () => {
            //第二引数に値を指定する必要がある
            close.removeEventListener('click', deleteOnClick);
          });
        }
      });
  }
});

calendar.render();