// BMW Technology - News Pages Script
$(document).ready(function() {
  // News data (Vietnamese and Japanese)
  const newsData = {
    vi: [
      { id: 1, title: 'Công nghệ BMW giúp giảm 95% ô nhiễm tại trang trại lớn nhất miền Bắc', excerpt: 'Sau 6 tháng triển khai, hệ thống BMW đã xử lý thành công hơn 500,000 lít nước thải mỗi ngày, giúp trang trại đạt tiêu chuẩn môi trường quốc tế.', category: 'Công nghệ', image: 'images/tech-water-treatment.jpg', date: '22/12/2024', body: `
        <p>Sau 6 tháng triển khai, hệ thống BMW đã xử lý thành công hơn 500,000 lít nước thải mỗi ngày, giúp trang trại đạt tiêu chuẩn môi trường quốc tế.</p>
        <h2>Giới thiệu về dự án</h2>
        <p>Đây là một trong những dự án tiên phong áp dụng công nghệ BMW tại Việt Nam, được triển khai tại trang trại chăn nuôi lớn nhất miền Bắc với quy mô hơn 10,000 con lợn.</p>
        <h2>Kết quả đạt được</h2>
        <ul>
          <li>Giảm 95% ô nhiễm nguồn nước</li>
          <li>Xử lý 500,000 lít nước thải mỗi ngày</li>
          <li>Tiết kiệm 60% chi phí xử lý môi trường</li>
          <li>Đạt chứng nhận ISO 14001</li>
        </ul>
        <h2>Công nghệ được sử dụng</h2>
        <p>Hệ thống BMW sử dụng vi sinh vật có lợi kết hợp với khoáng chất tự nhiên để phân hủy chất hữu cơ trong nước thải, tạo ra nước sinh học có thể tái sử dụng cho nông nghiệp.</p>
        <blockquote>
          "Công nghệ BMW đã thay đổi hoàn toàn cách chúng tôi xử lý chất thải. Giờ đây, nước thải không còn là vấn đề mà trở thành nguồn tài nguyên quý giá."
          <cite>— Giám đốc trang trại</cite>
        </blockquote>
        <h2>Kế hoạch mở rộng</h2>
        <p>Dựa trên thành công của dự án này, BMW Technology đang lên kế hoạch triển khai tại 50 trang trại khác trên toàn quốc trong năm 2025.</p>
      ` },
      { id: 2, title: 'Hướng dẫn sử dụng nước sinh học BMW trong canh tác rau hữu cơ', excerpt: 'Nước sinh học BMW có thể tăng năng suất cây trồng lên 30% mà không cần phân bón hóa học, đảm bảo an toàn thực phẩm cho người tiêu dùng.', category: 'Ứng dụng', image: 'images/app-agriculture.jpg', date: '20/12/2024', body: `
        <p>Nước sinh học BMW có thể tăng năng suất cây trồng lên 30% mà không cần phân bón hóa học, đảm bảo an toàn thực phẩm cho người tiêu dùng.</p>
        <h2>Lợi ích của nước sinh học BMW</h2>
        <ul>
          <li>Tăng năng suất cây trồng lên 30%</li>
          <li>Không cần sử dụng phân bón hóa học</li>
          <li>Cải thiện chất lượng đất trồng</li>
          <li>An toàn cho người tiêu dùng</li>
        </ul>
        <h2>Cách sử dụng</h2>
        <p>Pha loãng nước sinh học BMW với tỷ lệ 1:100 và tưới đều lên cây trồng 2-3 lần mỗi tuần để đạt hiệu quả tốt nhất.</p>
      ` },
      { id: 3, title: 'BMW Technology tham gia triển lãm Nông nghiệp Quốc tế 2024', excerpt: 'Giới thiệu công nghệ sinh học Nhật Bản đến hàng nghìn nông dân và doanh nghiệp Việt Nam tại triển lãm lớn nhất trong năm.', category: 'Sự kiện', image: 'images/environmental-impact.jpg', date: '18/12/2024', body: `
        <p>Giới thiệu công nghệ sinh học Nhật Bản đến hàng nghìn nông dân và doanh nghiệp Việt Nam tại triển lãm lớn nhất trong năm.</p>
        <h2>Điểm nhấn sự kiện</h2>
        <p>BMW Technology đã thu hút sự quan tâm của hơn 5,000 khách tham quan với gian hàng trưng bày công nghệ xử lý nước thải tiên tiến.</p>
        <h2>Phản hồi từ khách tham quan</h2>
        <blockquote>
          "Đây là giải pháp mà nông nghiệp Việt Nam đang rất cần."
          <cite>— Đại diện Bộ Nông nghiệp</cite>
        </blockquote>
      ` },
      { id: 4, title: 'Nghiên cứu mới: Vi sinh vật BMW giúp cải thiện chất lượng đất trồng', excerpt: 'Các nhà khoa học đã chứng minh vi sinh vật trong nước BMW có khả năng phục hồi đất bạc màu sau 3 tháng sử dụng liên tục.', category: 'Nghiên cứu', image: 'images/microscope-bacteria.jpg', date: '15/12/2024', body: `
        <p>Các nhà khoa học đã chứng minh vi sinh vật trong nước BMW có khả năng phục hồi đất bạc màu sau 3 tháng sử dụng liên tục.</p>
        <h2>Kết quả nghiên cứu</h2>
        <ul>
          <li>Tăng độ pH đất từ 4.5 lên 6.5</li>
          <li>Tăng hàm lượng chất hữu cơ 40%</li>
          <li>Cải thiện cấu trúc đất</li>
        </ul>
      ` },
      { id: 5, title: 'BMW Technology ký kết hợp tác chiến lược với 5 trang trại lớn tại miền Trung', excerpt: 'Thỏa thuận hợp tác nhằm mở rộng ứng dụng công nghệ BMW trong xử lý nước thải chăn nuôi quy mô công nghiệp.', category: 'Đối tác', image: 'images/tech-industrial-water.jpg', date: '12/12/2024', body: `
        <p>Thỏa thuận hợp tác nhằm mở rộng ứng dụng công nghệ BMW trong xử lý nước thải chăn nuôi quy mô công nghiệp.</p>
        <h2>Chi tiết hợp tác</h2>
        <p>5 trang trại với tổng đàn hơn 50,000 con lợn sẽ được trang bị hệ thống BMW trong năm 2025.</p>
      ` },
      { id: 6, title: 'Hệ thống BMW đạt chứng nhận ISO 14001 về quản lý môi trường', excerpt: 'Đây là minh chứng cho cam kết của BMW Technology trong việc bảo vệ môi trường và phát triển bền vững.', category: 'Thành tựu', image: 'images/circular-ecosystem.jpg', date: '10/12/2024', body: `
        <p>Đây là minh chứng cho cam kết của BMW Technology trong việc bảo vệ môi trường và phát triển bền vững.</p>
        <h2>Ý nghĩa chứng nhận</h2>
        <p>ISO 14001 là tiêu chuẩn quốc tế về hệ thống quản lý môi trường, khẳng định BMW Technology đáp ứng các yêu cầu nghiêm ngặt nhất về bảo vệ môi trường.</p>
      ` }
    ],
    jp: [
      { id: 1, title: 'BMW技術により北部最大の農場で95%の汚染削減を達成', excerpt: '導入から6ヶ月後、BMWシステムは毎日50万リットル以上の廃水を処理し、農場が国際環境基準を達成するのに貢献しています。', category: '技術', image: 'images/tech-water-treatment.jpg', date: '22/12/2024', body: `
        <p>導入から6ヶ月後、BMWシステムは毎日50万リットル以上の廃水を処理し、農場が国際環境基準を達成するのに貢献しています。</p>
        <h2>プロジェクト紹介</h2>
        <p>これは、ベトナムでBMW技術を適用した先駆的なプロジェクトの一つで、10,000頭以上の豚を飼育する北部最大の農場で実施されました。</p>
        <h2>達成した成果</h2>
        <ul>
          <li>水源汚染を95%削減</li>
          <li>毎日50万リットルの廃水を処理</li>
          <li>環境処理コストを60%削減</li>
          <li>ISO 14001認証を取得</li>
        </ul>
        <h2>使用技術</h2>
        <p>BMWシステムは、有益な微生物と天然鉱物を組み合わせて廃水中の有機物を分解し、農業に再利用可能な生物水を生成します。</p>
        <blockquote>
          「BMW技術は、私たちの廃棄物処理方法を完全に変えました。今では、廃水は問題ではなく、貴重な資源となっています。」
          <cite>— 農場ディレクター</cite>
        </blockquote>
        <h2>拡大計画</h2>
        <p>このプロジェクトの成功に基づき、BMW Technologyは2025年に全国50の農場に展開する計画を立てています。</p>
      ` },
      { id: 2, title: '有機野菜栽培におけるBMW生物水の使用ガイド', excerpt: 'BMW生物水は化学肥料なしで作物の収穫量を30%向上させ、消費者の食品安全を確保することができます。', category: '応用', image: 'images/app-agriculture.jpg', date: '20/12/2024', body: `
        <p>BMW生物水は化学肥料なしで作物の収穫量を30%向上させ、消費者の食品安全を確保することができます。</p>
        <h2>BMW生物水の利点</h2>
        <ul>
          <li>作物の収穫量を30%向上</li>
          <li>化学肥料が不要</li>
          <li>土壌品質を改善</li>
          <li>消費者の安全を確保</li>
        </ul>
        <h2>使用方法</h2>
        <p>BMW生物水を1:100の割合で希釈し、週に2〜3回作物に均等に散布することで、最良の効果が得られます。</p>
      ` },
      { id: 3, title: 'BMW Technologyが2024年国際農業博覧会に参加', excerpt: '年間最大の展示会で、日本のバイオテクノロジーを何千人ものベトナムの農家や企業に紹介しました。', category: 'イベント', image: 'images/environmental-impact.jpg', date: '18/12/2024', body: `
        <p>年間最大の展示会で、日本のバイオテクノロジーを何千人ものベトナムの農家や企業に紹介しました。</p>
        <h2>イベントのハイライト</h2>
        <p>BMW Technologyは、先進的な廃水処理技術を展示するブースで5,000人以上の来場者の関心を集めました。</p>
        <h2>来場者からのフィードバック</h2>
        <blockquote>
          「これはベトナム農業が本当に必要としているソリューションです。」
          <cite>— 農業省代表</cite>
        </blockquote>
      ` },
      { id: 4, title: '新研究：BMW微生物が土壌品質を改善', excerpt: '科学者たちは、BMW水中の微生物が3ヶ月間の継続使用で劣化した土壌を回復させる能力を持つことを証明しました。', category: '研究', image: 'images/microscope-bacteria.jpg', date: '15/12/2024', body: `
        <p>科学者たちは、BMW水中の微生物が3ヶ月間の継続使用で劣化した土壌を回復させる能力を持つことを証明しました。</p>
        <h2>研究結果</h2>
        <ul>
          <li>土壌pHを4.5から6.5に増加</li>
          <li>有機物含有量を40%増加</li>
          <li>土壌構造を改善</li>
        </ul>
      ` },
      { id: 5, title: 'BMW Technologyが中部地域の大規模農場5社と戦略的提携を締結', excerpt: '産業規模の畜産廃水処理におけるBMW技術の応用を拡大するための提携契約を締結しました。', category: 'パートナー', image: 'images/tech-industrial-water.jpg', date: '12/12/2024', body: `
        <p>産業規模の畜産廃水処理におけるBMW技術の応用を拡大するための提携契約を締結しました。</p>
        <h2>提携の詳細</h2>
        <p>合計5万頭以上の豚を飼育する5つの農場に、2025年にBMWシステムが導入されます。</p>
      ` },
      { id: 6, title: 'BMWシステムがISO 14001環境管理認証を取得', excerpt: 'これは、環境保護と持続可能な開発に対するBMW Technologyのコミットメントの証です。', category: '成果', image: 'images/circular-ecosystem.jpg', date: '10/12/2024', body: `
        <p>これは、環境保護と持続可能な開発に対するBMW Technologyのコミットメントの証です。</p>
        <h2>認証の意義</h2>
        <p>ISO 14001は環境マネジメントシステムの国際規格であり、BMW Technologyが環境保護に関する最も厳しい要件を満たしていることを確認しています。</p>
      ` }
    ]
  };

  // Translations
  const translations = {
    vi: {
      newsBadge: 'Tin Tức',
      newsPageTitle: 'Tất Cả Tin Tức',
      newsPageSubtitle: 'Cập nhật thông tin mới nhất về công nghệ BMW và nông nghiệp bền vững',
      readMore: 'Đọc thêm',
      backToNews: 'Quay lại danh sách',
      backToHome: 'Về trang chủ',
      relatedNews: 'Tin Tức Liên Quan',
      share: 'Chia sẻ:',
      home: 'Trang chủ',
      footerCopyright: '© 2025 BMW Technology. Bảo lưu mọi quyền.'
    },
    jp: {
      newsBadge: 'ニュース',
      newsPageTitle: 'すべてのニュース',
      newsPageSubtitle: 'BMW技術と持続可能な農業に関する最新情報',
      readMore: '続きを読む',
      backToNews: 'リストに戻る',
      backToHome: 'ホームに戻る',
      relatedNews: '関連ニュース',
      share: 'シェア:',
      home: 'ホーム',
      footerCopyright: '© 2025 BMW Technology. 全著作権所有。'
    }
  };

  // Get language from localStorage or default to 'vi'
  let currentLang = localStorage.getItem('bmw_lang') || 'vi';

  // Get URL parameter
  function getUrlParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  }

  // Render news card
  function renderNewsCard(news, lang) {
    const t = translations[lang];
    return `
      <article class="news-card">
        <a href="news-detail.html?id=${news.id}" class="news-card-link-wrap">
          <div class="news-card-image">
            <img src="${news.image}" alt="${news.title}">
            <div class="news-card-category">${news.category}</div>
          </div>
          <div class="news-card-content">
            <div class="news-card-meta">
              <span class="news-card-date">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>${news.date}
              </span>
            </div>
            <h3 class="news-card-title">${news.title}</h3>
            <p class="news-card-excerpt">${news.excerpt}</p>
            <span class="news-card-link">${t.readMore} <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
          </div>
        </a>
      </article>
    `;
  }

  // Render news list page
  function renderNewsList(lang) {
    const $grid = $('#newsListGrid');
    if ($grid.length) {
      $grid.empty();
      newsData[lang].forEach(function(news) {
        $grid.append(renderNewsCard(news, lang));
      });
    }
  }

  // Render news detail page
  function renderNewsDetail(lang) {
    const newsId = parseInt(getUrlParam('id')) || 1;
    const news = newsData[lang].find(n => n.id === newsId) || newsData[lang][0];
    
    if (news) {
      $('#articleTitle, #breadcrumbTitle').text(news.title);
      $('#articleCategory').text(news.category);
      $('#articleImage').attr('src', news.image).attr('alt', news.title);
      $('#articleBody').html(news.body);
      document.title = news.title + ' - BMW Technology';
    }

    // Render related news (exclude current)
    const $related = $('#relatedNews');
    if ($related.length) {
      $related.empty();
      newsData[lang]
        .filter(n => n.id !== newsId)
        .slice(0, 3)
        .forEach(function(news) {
          $related.append(renderNewsCard(news, lang));
        });
    }
  }

  // Update translations
  function updateTranslations(lang) {
    const t = translations[lang];
    $('[data-i18n]').each(function() {
      const key = $(this).data('i18n');
      if (t[key]) {
        $(this).text(t[key]);
      }
    });
  }

  // Set active button based on current language
  function setActiveButton(lang) {
    $('.lang-btn, .mobile-lang-btn').removeClass('active');
    $(`.lang-btn[data-lang="${lang}"], .mobile-lang-btn[data-lang="${lang}"]`).addClass('active');
  }

  // Initialize
  function init() {
    setActiveButton(currentLang);
    renderNewsList(currentLang);
    renderNewsDetail(currentLang);
    updateTranslations(currentLang);
  }

  // Language toggle (both desktop and mobile buttons)
  $('.lang-btn, .mobile-lang-btn').on('click', function() {
    const lang = $(this).data('lang');
    if (lang === currentLang) return;
    currentLang = lang;
    
    // Save to localStorage
    localStorage.setItem('bmw_lang', lang);
    
    setActiveButton(lang);
    renderNewsList(lang);
    renderNewsDetail(lang);
    updateTranslations(lang);
  });

  // Mobile menu toggle
  $('.mobile-menu-btn').on('click', function() {
    $('.mobile-menu, .mobile-menu-backdrop').addClass('show');
    $('body').css('overflow', 'hidden');
  });

  $('.mobile-menu-close, .mobile-menu-backdrop').on('click', function() {
    $('.mobile-menu, .mobile-menu-backdrop').removeClass('show');
    $('body').css('overflow', '');
  });

  // Initialize on page load
  init();
});
