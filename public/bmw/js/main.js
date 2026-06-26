// BMW Technology - jQuery Main Script
$(document).ready(function() {
  // Language content data
  const content = {
    vi: {
      nav: ['Công nghệ', 'Hệ thống', 'Sản phẩm', 'Ứng dụng', 'Tin tức', 'Liên hệ'],
      heroBadge: 'Công Nghệ Sinh Học Nhật Bản',
      heroTitle1: 'Biến Chất Thải Chăn Nuôi',
      heroTitle2: 'Thành Nước Sinh Học Có Giá Trị',
      heroSubtitle: 'Công nghệ BMW xử lý phân, nước tiểu, và nước rửa chuồng trại - Tạo ra nước sinh học đa năng cho nông nghiệp tuần hoàn bền vững',
      heroCta1: 'Khám Phá Công Nghệ',
      heroCta2: 'Xem Sản Phẩm',
      stat1: 'Giảm Ô Nhiễm',
      stat2: 'Nước Tái Sử Dụng',
      stat3: 'Tiết Kiệm Chi Phí',
      bmwBacteria: 'Vi khuẩn',
      bmwMineral: 'Khoáng chất',
      bmwWater: 'Nước',
      problemBadge: 'Thách Thức',
      problemTitle: 'Ô Nhiễm Chất Thải Chăn Nuôi',
      problems: [
        { title: 'Phân & Nước Tiểu', desc: 'Lượng phân và nước tiểu khổng lồ từ hàng nghìn con vật gây ô nhiễm nghiêm trọng' },
        { title: 'Nước Rửa Chuồng', desc: 'Nước thải từ làm sạch chuồng trại chứa vi khuẩn và hóa chất độc hại' },
        { title: 'Nguồn Nước Ô Nhiễm', desc: 'Chất thải thấm xuống đất và nước ngầm, làm nhiễm bẩn nguồn nước sạch' },
        { title: 'Mùi Hôi Thối', desc: 'Mùi khó chịu ảnh hưởng đến môi trường sống và sức khỏe cộng đồng' }
      ],
      warningTitle: 'Một trang trại chăn nuôi quy mô vừa có thể tạo ra đến 100,000 lít chất thải hàng ngày',
      warningText: 'Nếu không xử lý đúng cách, tác động môi trường là thảm khốc',
      coreTechBadge: 'Công Nghệ Cốt Lõi',
      coreTechTitle: 'BMW Technology',
      coreTechDesc: 'Công nghệ sinh học từ Nhật Bản, mô phỏng chu trình tuần hoàn tự nhiên để chuyển hóa chất thải thành tài nguyên có giá trị',
      techComponents: [
        { title: 'Bacteria', subtitle: 'Vi Khuẩn', desc: 'Vi sinh vật có lợi phân hủy chất hữu cơ, loại bỏ mùi hôi và làm sạch nước' },
        { title: 'Mineral', subtitle: 'Khoáng Chất', desc: 'Khoáng chất thiên nhiên cân bằng pH, kích hoạt vi sinh và tăng cường chất lượng nước' },
        { title: 'Water', subtitle: 'Nước', desc: 'Môi trường lý tưởng cho vi sinh hoạt động, tạo ra nước sinh học đa năng' }
      ],
      cycleTitle: 'Chu Trình Tuần Hoàn Tự Nhiên',
      cycleWaste: 'Chất Thải',
      cycleApp: 'Ứng Dụng',
      cycleBacteria: 'Vi Khuẩn',
      cycleWater: 'Nước Sinh Học',
      systemsBadge: 'Hệ Thống Công Nghệ',
      systemsTitle: 'Giải Pháp Công Nghệ',
      systemsSubtitle: 'Hệ thống xử lý tiêu chuẩn Nhật Bản - Bền vững, mở rộng, và hiệu quả cao',
      systems: [
        { title: 'Hệ Thống Xử Lý Nước Thải Chăn Nuôi', desc: 'Xử lý phân, nước tiểu và nước rửa chuồng quy mô lớn với công nghệ sinh học tiên tiến', features: ['Công suất 10-100m³/ngày', 'Tự động 100%', 'Đạt chuẩn xả thải'] },
        { title: 'Hệ Thống Cải Thiện Nước Uống Vật Nuôi', desc: 'Nâng cao chất lượng nước uống, giảm bệnh tật và tăng năng suất chăn nuôi', features: ['Kích hoạt vi sinh có lợi', 'Giảm stress động vật', 'Tăng sức đề kháng'] },
        { title: 'Hệ Thống Sản Xuất Nước Sinh Học', desc: 'Sản xuất nước sinh học BMW đa năng từ chất thải hoặc nguồn nước sạch', features: ['Sản xuất liên tục', 'Chất lượng ổn định', 'Quy mô linh hoạt'] }
      ],
      systemCtaTitle: 'Giải Pháp Tùy Chỉnh Cho Mọi Quy Mô',
      systemCtaText: 'Chúng tôi thiết kế hệ thống phù hợp với nhu cầu cụ thể của trang trại bạn',
      systemCtaBtn: 'Tư Vấn Miễn Phí',
      flowBadge: 'Chuyển Hóa',
      flowTitle: 'Từ Công Nghệ Đến Sản Phẩm',
      flowSteps: [
        { title: 'Nước Thải', subtitle: 'Phân, nước tiểu, nước rửa' },
        { title: 'Hệ Thống BMW', subtitle: 'Xử lý sinh học' },
        { title: 'Nước Sinh Học', subtitle: 'Sản phẩm cao cấp' }
      ],
      productBadge: 'Sản Phẩm',
      productTitle: 'Nước Sinh Học BMW',
      productSubtitle: 'Sản phẩm đa năng cao cấp từ công nghệ BMW',
      productLabel: 'Nước Sinh Học',
      productMulti: 'Sản Phẩm Đa Năng',
      productUses: [
        'Nông nghiệp: Tưới cây, cải tạo đất',
        'Chăn nuôi: Nước uống, khử mùi chuồng trại',
        'Thủy sản: Ổn định môi trường nước',
        'Môi trường: Xử lý chất thải, khử mùi'
      ],
      benefits: [
        { title: 'Kích Hoạt Vi Sinh Vật', desc: 'Thúc đẩy vi sinh vật có lợi trong đất, nước và môi trường sống' },
        { title: 'Giảm Ô Nhiễm & Mùi Hôi', desc: 'Loại bỏ mùi hôi hiệu quả, giảm ô nhiễm không khí và nước' },
        { title: 'Cải Thiện Sức Khỏe', desc: 'Tăng cường sức khỏe cây trồng, vật nuôi và sinh vật thủy sản' },
        { title: 'An Toàn & Tự Nhiên', desc: '100% sinh học, không hóa chất độc hại, an toàn tuyệt đối' }
      ],
      appBadge: 'Ứng Dụng',
      appTitle: 'Ứng Dụng Đa Dạng',
      apps: [
        { title: 'Nông Nghiệp', desc: 'Tưới cây, cải tạo đất, tăng năng suất' },
        { title: 'Chăn Nuôi', desc: 'Nước uống vật nuôi, khử mùi, cải thiện sức khỏe' },
        { title: 'Thủy Sản', desc: 'Ổn định nước, phòng bệnh, tăng năng suất' },
        { title: 'Môi Trường', desc: 'Xử lý chất thải, khử mùi, cải thiện không khí' }
      ],
      impactBadge: 'Tác Động',
      impactTitle: 'Tác Động Môi Trường & Kinh Tế',
      impacts: [
        { number: '100%', label: 'Tái Sử Dụng Nước', desc: 'Chuyển hóa 100% nước thải thành tài nguyên' },
        { number: '95%', label: 'Giảm Ô Nhiễm', desc: 'Loại bỏ chất ô nhiễm và mùi hôi hiệu quả' },
        { number: '60%', label: 'Tiết Kiệm Chi Phí', desc: 'Giảm chi phí xử lý và nước sạch' },
        { number: '∞', label: 'Tuần Hoàn Bền Vững', desc: 'Nông nghiệp tuần hoàn không lãng phí' }
      ],
      envTitle: 'Môi Trường',
      envItems: ['Bảo vệ nguồn nước ngầm và bề mặt', 'Giảm khí nhà kính từ chăn nuôi', 'Tạo chu trình sinh thái bền vững'],
      ecoTitle: 'Kinh Tế',
      ecoItems: ['Giảm chi phí xử lý nước thải', 'Tiết kiệm nước sạch và điện năng', 'Tạo giá trị từ chất thải'],
      ctaTitle1: 'Liên Hệ',
      ctaTitle2: 'Với Chúng Tôi',
      ctaDesc: 'Để lại thông tin để được tư vấn miễn phí về công nghệ BMW và giải pháp nông nghiệp bền vững',
      ctaChecks: ['Tư vấn miễn phí', 'Giải pháp tùy chỉnh', 'Hỗ trợ lâu dài'],
      formTitle: 'Đăng ký tư vấn',
      formName: 'Họ và tên của bạn',
      formPhone: 'Số điện thoại',
      formBtn: 'Gửi yêu cầu',
      formSuccess: 'Cảm ơn bạn! Chúng tôi sẽ liên hệ sớm.',
      footerTagline: 'Công nghệ sinh học Nhật Bản cho nông nghiệp tuần hoàn bền vững',
      footerTech: 'Công Nghệ',
      footerTechLinks: ['BMW Technology', 'Hệ thống xử lý', 'Hệ thống sản xuất', 'Tiêu chuẩn Nhật Bản'],
      footerApp: 'Ứng Dụng',
      footerAppLinks: ['Nông nghiệp', 'Chăn nuôi', 'Thủy sản', 'Môi trường'],
      footerPhone: '0123 456 789',
      footerEmail: 'info@bmwtechnology.vn',
      footerAddress: 'TP. Hà Nội, Việt Nam',
      footerCopyright: '© 2025 BMW Technology. Bảo lưu mọi quyền.',
      scrollTop: 'Lên đầu trang',
      newsBadge: 'Tin Tức',
      newsTitle: 'Tin Tức Mới Nhất',
      newsSubtitle: 'Cập nhật thông tin mới nhất về công nghệ BMW và nông nghiệp bền vững',
      newsReadMore: 'Đọc thêm',
      newsViewAll: 'Xem tất cả tin tức',
      newsItems: [
        { title: 'Công nghệ BMW giúp giảm 95% ô nhiễm tại trang trại lớn nhất miền Bắc', excerpt: 'Sau 6 tháng triển khai, hệ thống BMW đã xử lý thành công hơn 500,000 lít nước thải mỗi ngày, giúp trang trại đạt tiêu chuẩn môi trường quốc tế.', category: 'Công nghệ', date: '22/12/2024' },
        { title: 'Hướng dẫn sử dụng nước sinh học BMW trong canh tác rau hữu cơ', excerpt: 'Nước sinh học BMW có thể tăng năng suất cây trồng lên 30% mà không cần phân bón hóa học, đảm bảo an toàn thực phẩm cho người tiêu dùng.', category: 'Ứng dụng', date: '20/12/2024' },
        { title: 'BMW Technology tham gia triển lãm Nông nghiệp Quốc tế 2024', excerpt: 'Giới thiệu công nghệ sinh học Nhật Bản đến hàng nghìn nông dân và doanh nghiệp Việt Nam tại triển lãm lớn nhất trong năm.', category: 'Sự kiện', date: '18/12/2024' },
        { title: 'Nghiên cứu mới: Vi sinh vật BMW giúp cải thiện chất lượng đất trồng', excerpt: 'Các nhà khoa học đã chứng minh vi sinh vật trong nước BMW có khả năng phục hồi đất bạc màu sau 3 tháng sử dụng liên tục.', category: 'Nghiên cứu', date: '15/12/2024' },
        { title: 'BMW Technology ký kết hợp tác chiến lược với 5 trang trại lớn tại miền Trung', excerpt: 'Thỏa thuận hợp tác nhằm mở rộng ứng dụng công nghệ BMW trong xử lý nước thải chăn nuôi quy mô công nghiệp.', category: 'Đối tác', date: '12/12/2024' },
        { title: 'Hệ thống BMW đạt chứng nhận ISO 14001 về quản lý môi trường', excerpt: 'Đây là minh chứng cho cam kết của BMW Technology trong việc bảo vệ môi trường và phát triển bền vững.', category: 'Thành tựu', date: '10/12/2024' }
      ]
    },
    jp: {
      nav: ['技術', 'システム', '製品', '応用', 'ニュース', 'お問い合わせ'],
      heroBadge: '日本バイオテクノロジー',
      heroTitle1: '畜産廃水を',
      heroTitle2: '価値ある生物水に変換',
      heroSubtitle: 'BMW技術で糞尿・洗浄水を処理 - 循環型持続可能農業のための多目的生物水を生産',
      heroCta1: '技術を発見',
      heroCta2: '製品を見る',
      stat1: '汚染削減',
      stat2: '水の再利用',
      stat3: 'コスト削減',
      bmwBacteria: 'バクテリア',
      bmwMineral: 'ミネラル',
      bmwWater: '水',
      problemBadge: '課題',
      problemTitle: '畜産廃水汚染',
      problems: [
        { title: '糞尿', desc: '数千頭の家畜からの大量の糞尿が深刻な汚染を引き起こす' },
        { title: '洗浄水', desc: '畜舎清掃の廃水には細菌と有害化学物質が含まれる' },
        { title: '水源汚染', desc: '廃棄物が土壌や地下水に浸透し、清潔な水源を汚染する' },
        { title: '悪臭', desc: '不快な臭いが生活環境と地域の健康に影響を与える' }
      ],
      warningTitle: '中規模の畜産農場は1日最大10万リットルの廃棄物を生成します',
      warningText: '適切に処理しないと、環境への影響は壊滅的です',
      coreTechBadge: 'コア技術',
      coreTechTitle: 'BMW Technology',
      coreTechDesc: '日本のバイオテクノロジー、自然循環サイクルを模倣して廃棄物を価値ある資源に変換',
      techComponents: [
        { title: 'Bacteria', subtitle: 'バクテリア', desc: '有益な微生物が有機物を分解し、臭いを除去し、水を浄化する' },
        { title: 'Mineral', subtitle: 'ミネラル', desc: '天然鉱物がpHを調整し、微生物を活性化し、水質を向上させる' },
        { title: 'Water', subtitle: '水', desc: '微生物が機能する理想的な環境で、多目的生物水を生成' }
      ],
      cycleTitle: '自然循環サイクル',
      cycleWaste: '廃棄物',
      cycleApp: '応用',
      cycleBacteria: 'バクテリア',
      cycleWater: '生物水',
      systemsBadge: '技術システム',
      systemsTitle: '技術ソリューション',
      systemsSubtitle: '日本標準の処理システム - 持続可能、拡張可能、高効率',
      systems: [
        { title: '畜産廃水処理システム', desc: '先進的なバイオテクノロジーで大規模な糞尿と洗浄水を処理', features: ['容量 10-100m³/日', '100%自動化', '排出基準達成'] },
        { title: '家畜飲料水改善システム', desc: '飲料水の質を向上させ、病気を減らし、生産性を向上', features: ['有益な微生物の活性化', '動物のストレス軽減', '免疫力向上'] },
        { title: '生物水生産システム', desc: '廃棄物またはきれいな水源から多目的BMW生物水を生産', features: ['連続生産', '安定した品質', '柔軟なスケール'] }
      ],
      systemCtaTitle: 'あらゆる規模のカスタムソリューション',
      systemCtaText: 'お客様の農場の特定のニーズに合わせてシステムを設計します',
      systemCtaBtn: '無料相談',
      flowBadge: '変換',
      flowTitle: '技術から製品へ',
      flowSteps: [
        { title: '廃水', subtitle: '糞尿・洗浄水' },
        { title: 'BMWシステム', subtitle: '生物処理' },
        { title: '生物水', subtitle: 'プレミアム製品' }
      ],
      productBadge: '製品',
      productTitle: 'BMW生物水',
      productSubtitle: 'BMW技術からの多目的プレミアム製品',
      productLabel: '生物水',
      productMulti: '多目的製品',
      productUses: [
        '農業: 作物の灌漑、土壌改良',
        '畜産: 飲料水、畜舎の消臭',
        '水産: 水環境の安定化',
        '環境: 廃棄物処理、消臭'
      ],
      benefits: [
        { title: '微生物の活性化', desc: '土壌、水、生活環境の有益な微生物を促進' },
        { title: '汚染と悪臭の削減', desc: '効果的に悪臭を除去し、大気と水の汚染を削減' },
        { title: '健康の改善', desc: '作物、家畜、水産生物の健康を向上' },
        { title: '安全で自然', desc: '100%生物学的、有害化学物質なし、完全に安全' }
      ],
      appBadge: '応用',
      appTitle: '多様なアプリケーション',
      apps: [
        { title: '農業', desc: '作物灌漑、土壌改良、生産性向上' },
        { title: '畜産', desc: '飲料水、消臭、健康改善' },
        { title: '水産', desc: '水の安定化、病気予防、生産性向上' },
        { title: '環境', desc: '廃棄物処理、消臭、空気改善' }
      ],
      impactBadge: '影響',
      impactTitle: '環境・経済への影響',
      impacts: [
        { number: '100%', label: '水の再利用', desc: '100%の廃水を資源に変換' },
        { number: '95%', label: '汚染削減', desc: '汚染物質と悪臭を効果的に除去' },
        { number: '60%', label: 'コスト削減', desc: '処理と清潔な水のコストを削減' },
        { number: '∞', label: '持続可能な循環', desc: '無駄のない循環型農業' }
      ],
      envTitle: '環境',
      envItems: ['地下水と地表水の保護', '畜産からの温室効果ガスの削減', '持続可能な生態系サイクルの創造'],
      ecoTitle: '経済',
      ecoItems: ['廃水処理コストの削減', 'きれいな水と電力の節約', '廃棄物から価値を創造'],
      ctaTitle1: 'お問い合わせ',
      ctaTitle2: '',
      ctaDesc: 'BMW技術と持続可能な農業ソリューションについての無料相談のために情報をお寄せください',
      ctaChecks: ['無料相談', 'カスタムソリューション', '長期サポート'],
      formTitle: '相談申し込み',
      formName: 'お名前',
      formPhone: '電話番号',
      formBtn: 'リクエストを送信',
      formSuccess: 'ありがとうございます！すぐにご連絡いたします。',
      footerTagline: '循環型持続可能農業のための日本バイオテクノロジー',
      footerTech: '技術',
      footerTechLinks: ['BMW Technology', '処理システム', '生産システム', '日本基準'],
      footerApp: '応用',
      footerAppLinks: ['農業', '畜産', '水産', '環境'],
      footerPhone: '03-1234-5678',
      footerEmail: 'info@bmwtechnology.jp',
      footerAddress: '東京都, 日本',
      footerCopyright: '© 2025 BMW Technology. 全著作権所有。',
      scrollTop: 'トップへ',
      newsBadge: 'ニュース',
      newsTitle: '最新ニュース',
      newsSubtitle: 'BMW技術と持続可能な農業に関する最新情報',
      newsReadMore: '続きを読む',
      newsViewAll: 'すべてのニュースを見る',
      newsItems: [
        { title: 'BMW技術により北部最大の農場で95%の汚染削減を達成', excerpt: '導入から6ヶ月後、BMWシステムは毎日50万リットル以上の廃水を処理し、農場が国際環境基準を達成するのに貢献しています。', category: '技術', date: '22/12/2024' },
        { title: '有機野菜栽培におけるBMW生物水の使用ガイド', excerpt: 'BMW生物水は化学肥料なしで作物の収穫量を30%向上させ、消費者の食品安全を確保することができます。', category: '応用', date: '20/12/2024' },
        { title: 'BMW Technologyが2024年国際農業博覧会に参加', excerpt: '年間最大の展示会で、日本のバイオテクノロジーを何千人ものベトナムの農家や企業に紹介しました。', category: 'イベント', date: '18/12/2024' },
        { title: '新研究：BMW微生物が土壌品質を改善', excerpt: '科学者たちは、BMW水中の微生物が3ヶ月間の継続使用で劣化した土壌を回復させる能力を持つことを証明しました。', category: '研究', date: '15/12/2024' },
        { title: 'BMW Technologyが中部地域の大規模農場5社と戦略的提携を締結', excerpt: '産業規模の畜産廃水処理におけるBMW技術の応用を拡大するための提携契約を締結しました。', category: 'パートナー', date: '12/12/2024' },
        { title: 'BMWシステムがISO 14001環境管理認証を取得', excerpt: 'これは、環境保護と持続可能な開発に対するBMW Technologyのコミットメントの証です。', category: '成果', date: '10/12/2024' }
      ]
    }
  };

  // Get language from localStorage or default to 'vi'
  let currentLang = localStorage.getItem('bmw_lang') || 'vi';

  // Set active language button
  function setActiveLangButton(lang) {
    $('.lang-btn, .mobile-lang-btn').removeClass('active');
    $(`.lang-btn[data-lang="${lang}"], .mobile-lang-btn[data-lang="${lang}"]`).addClass('active');
  }

  // Initialize language on page load
  setActiveLangButton(currentLang);
  updateContent(currentLang);

  // Update content function
  function updateContent(lang) {
    const t = content[lang];
    
    // Navigation
    $('.nav-link').each(function(i) { $(this).text(t.nav[i]); });
    $('.mobile-nav-link').each(function(i) { $(this).text(t.nav[i]); });
    
    // Hero
    $('[data-i18n="heroBadge"]').text(t.heroBadge);
    $('[data-i18n="heroTitle1"]').text(t.heroTitle1);
    $('[data-i18n="heroTitle2"]').text(t.heroTitle2);
    $('[data-i18n="heroSubtitle"]').text(t.heroSubtitle);
    $('[data-i18n="heroCta1"]').text(t.heroCta1);
    $('[data-i18n="heroCta2"]').text(t.heroCta2);
    $('[data-i18n="stat1"]').text(t.stat1);
    $('[data-i18n="stat2"]').text(t.stat2);
    $('[data-i18n="stat3"]').text(t.stat3);
    $('[data-i18n="bmwBacteria"]').text(t.bmwBacteria);
    $('[data-i18n="bmwMineral"]').text(t.bmwMineral);
    $('[data-i18n="bmwWater"]').text(t.bmwWater);
    
    // Problem
    $('[data-i18n="problemBadge"]').text(t.problemBadge);
    $('[data-i18n="problemTitle"]').text(t.problemTitle);
    $('.problem-card').each(function(i) {
      $(this).find('.card-title').text(t.problems[i].title);
      $(this).find('.card-text').text(t.problems[i].desc);
    });
    $('[data-i18n="warningTitle"]').text(t.warningTitle);
    $('[data-i18n="warningText"]').text(t.warningText);
    
    // Core Technology
    $('[data-i18n="coreTechBadge"]').text(t.coreTechBadge);
    $('[data-i18n="coreTechDesc"]').text(t.coreTechDesc);
    $('.tech-component').each(function(i) {
      $(this).find('.tech-card-subtitle').text(t.techComponents[i].subtitle);
      $(this).find('.tech-card-text').text(t.techComponents[i].desc);
    });
    $('[data-i18n="cycleTitle"]').text(t.cycleTitle);
    $('[data-i18n="cycleWaste"]').text(t.cycleWaste);
    $('[data-i18n="cycleApp"]').text(t.cycleApp);
    $('[data-i18n="cycleBacteria"]').text(t.cycleBacteria);
    $('[data-i18n="cycleWater"]').text(t.cycleWater);
    
    // Systems
    $('[data-i18n="systemsBadge"]').text(t.systemsBadge);
    $('[data-i18n="systemsTitle"]').text(t.systemsTitle);
    $('[data-i18n="systemsSubtitle"]').text(t.systemsSubtitle);
    $('.system-item').each(function(i) {
      $(this).find('.system-title').text(t.systems[i].title);
      $(this).find('.system-desc').text(t.systems[i].desc);
      $(this).find('.system-feature-text').each(function(j) {
        $(this).text(t.systems[i].features[j]);
      });
    });
    $('[data-i18n="systemCtaTitle"]').text(t.systemCtaTitle);
    $('[data-i18n="systemCtaText"]').text(t.systemCtaText);
    $('[data-i18n="systemCtaBtn"]').text(t.systemCtaBtn);
    
    // Flow
    $('[data-i18n="flowBadge"]').text(t.flowBadge);
    $('[data-i18n="flowTitle"]').text(t.flowTitle);
    $('.flow-step').each(function(i) {
      $(this).find('.flow-title').text(t.flowSteps[i].title);
      $(this).find('.flow-subtitle').text(t.flowSteps[i].subtitle);
    });
    
    // Product
    $('[data-i18n="productBadge"]').text(t.productBadge);
    $('[data-i18n="productTitle"]').text(t.productTitle);
    $('[data-i18n="productSubtitle"]').text(t.productSubtitle);
    $('[data-i18n="productLabel"]').text(t.productLabel);
    $('[data-i18n="productMulti"]').text(t.productMulti);
    $('.product-use').each(function(i) { $(this).text(t.productUses[i]); });
    $('.benefit-item').each(function(i) {
      $(this).find('.benefit-title').text(t.benefits[i].title);
      $(this).find('.benefit-text').text(t.benefits[i].desc);
    });
    
    // Applications
    $('[data-i18n="appBadge"]').text(t.appBadge);
    $('[data-i18n="appTitle"]').text(t.appTitle);
    $('.app-item').each(function(i) {
      $(this).find('.app-card-title').text(t.apps[i].title);
      $(this).find('.app-card-text').text(t.apps[i].desc);
    });
    
    // Impact
    $('[data-i18n="impactBadge"]').text(t.impactBadge);
    $('[data-i18n="impactTitle"]').text(t.impactTitle);
    $('.impact-item').each(function(i) {
      $(this).find('.impact-number').text(t.impacts[i].number);
      $(this).find('.impact-label').text(t.impacts[i].label);
      $(this).find('.impact-text').text(t.impacts[i].desc);
    });
    $('[data-i18n="envTitle"]').text(t.envTitle);
    $('.env-item').each(function(i) { $(this).find('span').last().text(t.envItems[i]); });
    $('[data-i18n="ecoTitle"]').text(t.ecoTitle);
    $('.eco-item').each(function(i) { $(this).find('span').last().text(t.ecoItems[i]); });
    
    // News
    $('[data-i18n="newsBadge"]').text(t.newsBadge);
    $('[data-i18n="newsTitle"]').text(t.newsTitle);
    $('[data-i18n="newsSubtitle"]').text(t.newsSubtitle);
    $('[data-i18n="newsReadMore"]').text(t.newsReadMore);
    $('[data-i18n="newsViewAll"]').text(t.newsViewAll);
    // Update news items content
    if (t.newsItems) {
      $('.news-card').each(function(i) {
        if (t.newsItems[i]) {
          $(this).find('.news-card-title').text(t.newsItems[i].title);
          $(this).find('.news-card-excerpt').text(t.newsItems[i].excerpt);
          $(this).find('.news-card-category').text(t.newsItems[i].category);
        }
      });
    }
    
    // CTA
    $('[data-i18n="ctaTitle1"]').text(t.ctaTitle1);
    $('[data-i18n="ctaTitle2"]').text(t.ctaTitle2);
    $('[data-i18n="ctaDesc"]').text(t.ctaDesc);
    $('.cta-check span').each(function(i) { $(this).text(t.ctaChecks[i]); });
    $('[data-i18n="formTitle"]').text(t.formTitle);
    $('[data-i18n="formName"]').attr('placeholder', t.formName);
    $('[data-i18n="formPhone"]').attr('placeholder', t.formPhone);
    $('[data-i18n="formBtn"]').html('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>' + t.formBtn);
    $('[data-i18n="formSuccess"]').text(t.formSuccess);
    
    // Footer
    $('[data-i18n="footerTagline"]').text(t.footerTagline);
    $('[data-i18n="footerTech"]').text(t.footerTech);
    $('.footer-tech-link').each(function(i) { $(this).text(t.footerTechLinks[i]); });
    $('[data-i18n="footerApp"]').text(t.footerApp);
    $('.footer-app-link').each(function(i) { $(this).text(t.footerAppLinks[i]); });
    $('[data-i18n="footerPhone"]').text(t.footerPhone);
    $('[data-i18n="footerEmail"]').text(t.footerEmail);
    $('[data-i18n="footerAddress"]').text(t.footerAddress);
    $('[data-i18n="footerCopyright"]').text(t.footerCopyright);
    $('[data-i18n="scrollTop"]').text(t.scrollTop);
  }

  // Language toggle
  $('.lang-btn, .mobile-lang-btn').on('click', function() {
    const lang = $(this).data('lang');
    if (lang === currentLang) return;
    currentLang = lang;
    
    // Save to localStorage
    localStorage.setItem('bmw_lang', lang);
    
    setActiveLangButton(lang);
    updateContent(lang);
  });

  // Header scroll effect + Scroll Spy
  const sections = ['technology', 'systems', 'products', 'applications', 'news', 'contact'];
  
  function updateActiveNav() {
    const scrollPos = $(window).scrollTop() + 150;
    
    // Find current section (the one whose top is closest but above scroll position)
    let currentSection = '';
    let closestDistance = Infinity;
    
    sections.forEach(sectionId => {
      const section = $(`#${sectionId}`);
      if (section.length) {
        const sectionTop = section.offset().top;
        const distance = scrollPos - sectionTop;
        
        // If we've scrolled past the section top and it's the closest one
        if (distance >= 0 && distance < closestDistance) {
          closestDistance = distance;
          currentSection = sectionId;
        }
      }
    });
    
    // Update nav links
    $('.nav-link, .mobile-nav-link').removeClass('active');
    if (currentSection) {
      $(`.nav-link[href="#${currentSection}"], .mobile-nav-link[href="#${currentSection}"]`).addClass('active');
    }
  }

  $(window).on('scroll', function() {
    if ($(this).scrollTop() > 20) {
      $('.header').addClass('scrolled');
    } else {
      $('.header').removeClass('scrolled');
    }
    
    // Scroll to top button
    if ($(this).scrollTop() > 400) {
      $('.quick-btn.scroll-top').addClass('show');
    } else {
      $('.quick-btn.scroll-top').removeClass('show');
    }
    
    // Update active nav
    updateActiveNav();
  });
  
  // Initial check
  updateActiveNav();

  // Mobile menu toggle
  $('.mobile-menu-btn').on('click', function() {
    $('.mobile-menu-backdrop').addClass('show');
    $('.mobile-menu').addClass('show');
  });

  $('.mobile-menu-close, .mobile-menu-backdrop, .mobile-nav-link').on('click', function() {
    $('.mobile-menu').removeClass('show');
    $('.mobile-menu-backdrop').removeClass('show');
  });

  // Smooth scroll
  $('a[href^="#"]').on('click', function(e) {
    e.preventDefault();
    const target = $(this.getAttribute('href'));
    if (target.length) {
      const offsetTop = target.offset().top - 80;
      window.scrollTo({ top: offsetTop, behavior: 'smooth' });
    }
  });

  // Logo click - scroll to top
  $('.logo').on('click', function(e) {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Scroll to top button
  $('.quick-btn.scroll-top').on('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Scroll animations with Intersection Observer
  const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('scroll-visible');
        entry.target.classList.remove('scroll-hidden');
      }
    });
  }, observerOptions);

  $('.scroll-animate').each(function() {
    this.classList.add('scroll-hidden');
    observer.observe(this);
  });

  // Form handling
  $('#contact-form').on('submit', function(e) {
    e.preventDefault();
    const name = $('[data-i18n="formName"]').val().trim();
    const phone = $('[data-i18n="formPhone"]').val().trim();
    
    if (!name || !phone) return;
    
    const $btn = $(this).find('.cta-form-btn');
    $btn.prop('disabled', true).html('<div class="cta-form-spinner"></div>');
    
    setTimeout(() => {
      $('.cta-form-fields').addClass('hide');
      $('.cta-form-success').addClass('show');
      console.log('Form submitted:', { name, phone });
    }, 1000);
  });
});
