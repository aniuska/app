package com.wikia.webdriver.TestCases.ArticleCRUDTests;

import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;

import com.wikia.webdriver.Common.Core.CommonFunctions;
import com.wikia.webdriver.Common.Core.Global;
import com.wikia.webdriver.Common.Properties.Properties;
import com.wikia.webdriver.Common.Templates.TestTemplate;
import com.wikia.webdriver.PageObjects.PageObject.WikiBasePageObject;
import com.wikia.webdriver.PageObjects.PageObject.WikiPage.WikiArticleEditMode;
import com.wikia.webdriver.PageObjects.PageObject.WikiPage.WikiArticlePageObject;

public class ArticleTestsAdmin extends TestTemplate{
	
	private String pageName;
	private String articleText = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.";
	private String articleTextEdit = "Brand new content";
	private String commentText = "Lorem ipsum dolor sit amet, comment";
	private String commentTextEdit = "Brand new comment";
	private String replyText = "Brand new reply";
	
	
	/*
	 * TestCase002
	 * Open random wiki page as logged in user
	 * Click edit drop-down
	 * Verify available edit options for logged in user (history and rename)
	 */
	@Test(groups={"ArticleCRUDAdmin_002", "ArticleCRUDAdmin"})
	public void ArticleCRUDAdmin_002_VerifyEditDropDown_LoggedInUser()
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userName, Properties.password);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		wiki.openWikiPage();
		wiki.openRandomArticle();
		wiki.clickEditDropDown();
		wiki.verifyEditDropDownLoggedInUser();
		CommonFunctions.logOut(Properties.userName, driver);
	}
	
	/*
	 * TestCase003
	 * Open random wiki page as admin user
	 * Click edit drop-down
	 * Verify available edit options for admin user (history, rename, protect, delete)
	 */
	@Test(groups={"ArticleCRUDAdmin_003", "ArticleCRUDAdmin"})
	public void ArticleCRUDAdmin_003_VerifyEditDropDown_AdminUser()
	{
		CommonFunctions.logOut(Properties.userNameStaff, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		wiki.openWikiPage();
		wiki.openRandomArticle();
		wiki.clickEditDropDown();
		wiki.verifyEditDropDownAdmin();
		CommonFunctions.logOut(Properties.userNameStaff, driver);
	}
	
	
	/*
	 * TestCase004
	 * Create article as admin user with following names:
	 * 	normal: QAarticle
	 * 	non-latin: 這是文章的名字在中國
	 * 	long: QAVeryLongArticleNameQAVeryLongArticleNameQAVeryLongArticleNameQAVeryLongArticleNameQAVeryLongArticleNameQAVeryLongArticleName
	 * 	with slash: QA/article
	 * 	with underscore: QA_article
	 * 	made from digits:123123123123
	 * Delete article
	 */
			
	@Test(dataProvider="getArticleName", groups={"ArticleCRUDAdmin_004", "ArticleCRUDAdmin"})
	public void ArticleCRUDAdmin_004_CreateArticle_Admin(String articleName)
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		pageName = articleName+wiki.getTimeStamp();
		wiki.openWikiPage();
		WikiArticleEditMode edit = wiki.createNewArticle(pageName, 1);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.typeInContent(articleText);
		WikiArticlePageObject article = edit.clickOnPublishButton();
		article.verifyPageTitle(pageName);
		article.verifyArticleText(articleText);
		article.deleteArticle();
		article.openArticle(pageName);
		article.verifyDeletedArticlePage(pageName);
		CommonFunctions.logOut(Properties.userNameStaff, driver);
	}
	/*
	 * TestCase005
	 * Create article as admin user
	 * Edit article
	 * Delete article
	 */
	@Test(groups={"ArticleCRUDAdmin_005", "ArticleCRUDAdmin"})
	public void ArticleCRUDAdmin_005_CreateEditArticle_Admin()
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		pageName = "QAarticle"+wiki.getTimeStamp();
		wiki.openWikiPage();
		WikiArticleEditMode edit = wiki.createNewArticle(pageName, 1);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.typeInContent(articleText);
		WikiArticlePageObject article = edit.clickOnPublishButton();
		article.verifyPageTitle(pageName);
		article.verifyArticleText(articleText);
		edit = article.clickEditButton(pageName);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.typeInContent(articleTextEdit);
		article = edit.clickOnPublishButton();
		article.verifyPageTitle(pageName);
		article.verifyArticleText(articleTextEdit);
		article.deleteArticle();
		article = article.openArticle(pageName);
		article.verifyDeletedArticlePage(pageName);
		CommonFunctions.logOut(Properties.userNameStaff, driver);
	}
	
	/* 
	 * TestCase006
	 * Add article as admin
	 * Add comment
	 * Delete comment
	 * Delete article
	 */
	@Test(groups={"ArticleCRUDAdmin_006", "ArticleCRUDAdmin"})
	public void ArticleCRUDAdmin_006_CreateArticleComment_Admin()
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		pageName = "QAarticle"+wiki.getTimeStamp();
		wiki.openWikiPage();
		WikiArticleEditMode edit = wiki.createNewArticle(pageName, 1);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.typeInContent(articleText);
		WikiArticlePageObject article = edit.clickOnPublishButton();
		edit.verifyPageTitle(pageName);
		article.triggerCommentArea();
		article.writeOnCommentArea(commentText);
		article.clickSubmitButton();
		article.verifyComment(commentText, Properties.userNameStaff);
		article.deleteComment(commentText);
		edit.deleteArticle();
		edit.openArticle(pageName);
		edit.verifyDeletedArticlePage(pageName);
		CommonFunctions.logOut(Properties.userNameStaff, driver);
	}
	/*
	 * TestCase007
	 * Add article
	 * Add comment
	 * Edit comment
	 * Delete comment
	 * Delete article
	 */
	@Test(groups={"ArticleCRUDAdmin_007", "ArticleCRUDAdmin"}) //P2 issue raised: https://wikia.fogbugz.com/default.asp?46789 article comments aren't visible in IE9
	public void ArticleCRUDAdmin_007_CreateArticleEditComment_007()
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		pageName = "QAarticle"+wiki.getTimeStamp();
		wiki.openWikiPage();
		WikiArticleEditMode edit = wiki.createNewArticle(pageName, 1);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.typeInContent(articleText);
		WikiArticlePageObject article = edit.clickOnPublishButton();
		edit.verifyPageTitle(pageName);
		article.triggerCommentArea();
		article.writeOnCommentArea(commentText);
		article.clickSubmitButton();
		article.verifyComment(commentText, Properties.userNameStaff);
		article.editComment(commentText);
		article.writeOnCommentArea(commentTextEdit);
		article.clickSubmitButton(Properties.userNameStaff);
		article.verifyComment(commentTextEdit, Properties.userNameStaff);
		article.deleteComment(commentTextEdit);
		edit.deleteArticle();
		edit.openArticle(pageName);
		edit.verifyDeletedArticlePage(pageName);
		CommonFunctions.logOut(Properties.userNameStaff, driver);
	}
	
	/*
	 * TestCase005
	 * Add article
	 * Delete article
	 * Undelete article
	 */
	
	@Test(groups={"ArticleCRUDAdmin_008", "ArticleCRUDAdmin"})
	public void ArticleCRUDAdmin_008_CreateArticleUndeleteDelete_Admin()
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		pageName = "QAarticle"+wiki.getTimeStamp();
		wiki.openWikiPage();
		WikiArticleEditMode edit = wiki.createNewArticle(pageName, 1);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.typeInContent(articleText);
		WikiArticlePageObject article = edit.clickOnPublishButton();
		article.verifyPageTitle(pageName);
		article.verifyArticleText(articleText);
		article.deleteArticle();
		article.undeleteArticle();
		article.openArticle(pageName);
		article.verifyPageTitle(pageName);
		article.verifyArticleText(articleText);
		article.deleteArticle();
		article.openArticle(pageName);
		article.verifyDeletedArticlePage(pageName);
		CommonFunctions.logOut(Properties.userNameStaff, driver);
	}
	/*
	 * TestCase006
	 * Add article
	 * Move-rename article
	 * Delete article
	 */
	
	@Test(groups={"ArticleCRUDAdmin_009", "ArticleCRUDAdmin"})
	public void ArticleCRUDAdmin_009_CreateArticleMoveDelete_Admin()
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		pageName = "QAarticle"+wiki.getTimeStamp();
		wiki.openWikiPage();
		WikiArticleEditMode edit = wiki.createNewArticle(pageName, 1);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.typeInContent(articleText);
		WikiArticlePageObject article = edit.clickOnPublishButton();
		article.verifyPageTitle(pageName);
		article.verifyArticleText(articleText);
		article.renameArticle(pageName, pageName+"moved");
		article.verifyPageTitle(pageName+"moved");
		article.verifyArticleText(articleText);
		article.deleteArticle();
		article.openArticle(pageName+"moved");
		article.verifyDeletedArticlePage(pageName+"moved");
		CommonFunctions.logOut(Properties.userNameStaff, driver);
	}
	
	/* 
	 * TestCase010
	 * Add article as admin
	 * Add comment
	 * Reply comment
	 * Delete comment
	 * Delete article
	 */
	@Test(groups={"ArticleCRUDAdmin_010", "ArticleCRUDAdmin"})
	public void ArticleCRUDAdmin_010_CreateArticleCommentReply_Admin()
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		pageName = "QAarticle"+wiki.getTimeStamp();
		wiki.openWikiPage();
		WikiArticleEditMode edit = wiki.createNewArticle(pageName, 1);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.typeInContent(articleText);
		WikiArticlePageObject article = edit.clickOnPublishButton();
		edit.verifyPageTitle(pageName);
		article.triggerCommentArea();
		article.writeOnCommentArea(commentText);
		article.clickSubmitButton();
		article.verifyComment(commentText, Properties.userNameStaff);
		article.replyComment(commentText, replyText);
		article.deleteComment(commentText);
		edit.deleteArticle();
		edit.openArticle(pageName);
		edit.verifyDeletedArticlePage(pageName);
		CommonFunctions.logOut(Properties.userNameStaff, driver);
	}

	@Test(groups={"ArticleCRUDAdmin_011", "ArticleCRUDAdmin"}) 
//	https://internal.wikia-inc.com/wiki/QA/Core_Features_and_Testing/Manual_Regression_Tests/Image_Serving	
	// Test Case 007  Adding galleries to an article in edit mode
	public void ArticleCRUDAdmin_011_AddingGallery_Admin()
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		pageName = "QAarticle"+wiki.getTimeStamp();
		wiki.openWikiPage();
		WikiArticleEditMode edit = wiki.createNewArticle(pageName, 1);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.clickOnAddObjectButton("Gallery");
		edit.waitForObjectModalAndClickAddAphoto("Gallery");
		edit.galleryCheckImageInputs(4);
		edit.galleryClickOnSelectButton();
		edit.gallerySetPositionGallery("Center");//error!!!
		edit.gallerySetPhotoOrientation(2);
		edit.galleryClickOnFinishButton();
		edit.verifyObjectInEditMode("gallery");
		edit.clickOnPreviewButton();
		edit.verifyTheObjectOnThePreview("gallery");
		WikiArticlePageObject article = edit.clickOnPublishButtonPreview();
		article.VerifyTheObjectOnThePage("gallery");
		edit = article.Edit();
		edit.deleteArticleContent();
		article = edit.clickOnPublishButton();
		article.deleteArticle();
		article.openArticle(pageName);
		article.verifyDeletedArticlePage(pageName);
		CommonFunctions.logOut(Properties.userName2, driver);
		CommonFunctions.MoveCursorTo(0, 0);
	}
	
	@Test(groups={"ArticleCRUDAdmin_012", "ArticleCRUDAdmin"}) 
//	https://internal.wikia-inc.com/wiki/QA/Core_Features_and_Testing/Manual_Regression_Tests/Image_Serving	
	// Test Case 008 Adding slideshows to an article in edit mode
	public void ArticleCRUDAdmin_012_AddingSlideshow_Admin()
	{
		CommonFunctions.logOut(Properties.userName, driver);
		CommonFunctions.logIn(Properties.userNameStaff, Properties.passwordStaff);
		WikiBasePageObject wiki = new WikiBasePageObject(driver, Global.DOMAIN);
		pageName = "QAarticle"+wiki.getTimeStamp();
		wiki.openWikiPage();
		WikiArticleEditMode edit = wiki.createNewArticle(pageName, 1);
		edit.deleteArticleContent();
		edit.clickOnVisualButton();
		edit.clickOnAddObjectButton("Slideshow");
		edit.waitForObjectModalAndClickAddAphoto("GallerySlideshow");
		edit.galleryCheckImageInputs(4);
		edit.galleryClickOnSelectButton();
		edit.gallerySetPositionSlideshow("Center");
		edit.galleryClickOnFinishButton();
		edit.verifyObjectInEditMode("slideshow");
		edit.clickOnPreviewButton();
		edit.verifyTheObjectOnThePreview("slideshow");
		WikiArticlePageObject article = edit.clickOnPublishButtonInPreviewMode();
		article.VerifyTheObjectOnThePage("slideshow");
		edit = article.Edit();
		edit.deleteArticleContent();
		article = edit.clickOnPublishButton();
		article.deleteArticle();
		article.openArticle(pageName);
		article.verifyDeletedArticlePage(pageName);
		CommonFunctions.logOut(Properties.userNameStaff, driver);
	}
	
	@DataProvider
	private static final Object[][] getArticleName()
	{
		return new Object[][]
				{
					{"QAarticle"},
					{"這是文章的名字在中國"},
					{"QAVeryLongArticleNameQAVeryLongArticleNameQAVeryLongArticleNameQAVeryLongArticleNameQAVeryLongArticleNameQAVeryLongArticleName"},
					{"QA/article"},
					{"QA_article"},
					{"123123123123"}
				};
	}
}