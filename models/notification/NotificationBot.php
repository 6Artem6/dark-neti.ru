<?php

namespace app\models\notification;

use Yii;
use yii\base\Model;
use yii\helpers\{ArrayHelper, Html};

use app\models\question\{Question, Answer, Comment};
use app\models\user\User;
use app\models\request\Report;
use app\models\follow\{FollowQuestion, FollowUser, FollowDiscipline};
use app\models\service\Bot;


class NotificationBot extends Model
{

	protected static function getMessageParams(array $params): array
	{
		$text = null;
		$discipline = null;
		$message = null;
		$link = null;
		$type_id = ArrayHelper::getValue($params, 'type_id');
		$question = ArrayHelper::getValue($params, 'question');
		$answer = ArrayHelper::getValue($params, 'answer');
		$comment = ArrayHelper::getValue($params, 'comment');
		if ($type_id == Notification::MY_QUESTION_ANSWER) {
			$text = $question->question_title;
			$message = "Пользователь {name} дал ответ на Ваш вопрос \"{text}\".";
			$link = $answer->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_QUESTION_ANSWER) {
			$text = $question->question_title;
			$message = "Пользователь {name} дал ответ на вопрос, на который Вы подписаны, \"{text}\".";
			$link = $answer->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_USER_ANSWER) {
			$text = $question->question_title;
			$message = "Пользователь {name}, на которого Вы подписаны, дал ответ на вопрос \"{text}\".";
			$link = $answer->getRecordLink(true);
		} elseif ($type_id == Notification::MY_QUESTION_COMMENT) {
			$text = $question->question_title;
			$message = "Пользователь {name} оставил комментарий к Вашему вопросу \"{text}\".";
			$link = $comment->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_QUESTION_COMMENT) {
			$text = $question->question_title;
			$message = "Пользователь {name} оставил комментарий к вопросу \"{text}\", на который Вы подписаны.";
			$link = $comment->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_DISCIPLINE_QUESTION_COMMENT) {
			$text = $question->question_title;
			$message = "Пользователь {name} оставил комментарий к вопросу \"{text}\" по предмету {discipline}, на который Вы подписаны.";
			$link = $comment->getRecordLink(true);
			$discipline = $question->discipline->name;
		} elseif ($type_id == Notification::MY_QUESTION_ANSWER_COMMENT) {
			$text = $answer->shortText;
			$message = "Пользователь {name} оставил комментарий к Вашему ответу \"{text}\" на Ваш вопрос.";
			$link = $comment->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_QUESTION_ANSWER_COMMENT) {
			$text = $answer->shortText;
			$message = "Пользователь {name} оставил комментарий к ответу на вопрос \"{text}\", на который Вы подписаны.";
			$link = $comment->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_QUESTION_MY_ANSWER_COMMENT) {
			$text = $answer->shortText;
			$message = "Пользователь {name} оставил комментарий к Вашему ответу \"{text}\" на вопрос, на который Вы подписаны.";
			$link = $comment->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_DISCIPLINE_QUESTION_ANSWER_COMMENT) {
			$text = $answer->shortText;
			$message = "Пользователь {name} оставил комментарий к Вашему ответу \"{text}\" на вопрос по предмету {discipline}, на который Вы подписаны.";
			$link = $comment->getRecordLink(true);
			$discipline = $answer->question->discipline->name;
		} elseif ($type_id == Notification::FOLLOWED_QUESTION_ANSWERED) {
			$text = $question->question_title;
			$message = "Пользователь {name} решил вопрос \"{text}\", на который Вы подписаны.";
			$link = $answer->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_USER_QUESTION_ANSWERED) {
			$text = $question->question_title;
			$message = "Пользователь {name} решил вопрос \"{text}\" пользователя, на которого Вы подписаны.";
			$link = $answer->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_DISCIPLINE_QUESTION_ANSWERED) {
			$text = $question->question_title;
			$message = "Пользователь {name} решил вопрос \"{text}\" по предмету {discipline}, на который Вы подписаны.";
			$link = $answer->getRecordLink(true);
			$discipline = $question->discipline->name;
		} elseif ($type_id == Notification::FOLLOWED_USER_QUESTION_CREATE) {
			$text = $question->question_title;
			$message = "Пользователь {name}, на которого Вы подписаны, задал новый вопрос \"{text}\".";
			$link = $question->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_DISCIPLINE_QUESTION_CREATE) {
			$text = $question->question_title;
			$message = "Пользователь {name}, задал новый вопрос \"{text}\" по предмету {discipline}, на который Вы подписаны.";
			$link = $question->getRecordLink(true);
			$discipline = $question->discipline->name;
		} /*elseif ($type_id == Notification::MENTION) {
			$text = $answer->shortText;
			$message = "Пользователь {name} отметил вас при комментарии к ответу \"{text}\".";
			$link = $comment->getRecordLink(true);
		} elseif ($type_id == Notification::INVITE_AS_EXPERT) {
			$text = $question->question_title;
			$message = "Пользователь {name} пригласил Вас в качестве эксперта на вопрос \"{text}\".";
			$link = $question->getRecordLink(true);
		}*/ elseif ($type_id == Notification::MY_ANSWER_LIKE) {
			$text = $answer->shortText;
			$message = "Пользователь {name} отметил Ваш ответ \"{text}\" полезным.";
			$link = $answer->getRecordLink(true);
		} elseif ($type_id == Notification::ANSWER_EDIT) {
			$text = $answer->shortText;
			$message = "Пользователь {name} отредактировал свой ответ \"{text}\".";
			$link = $answer->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_QUESTION_EDIT) {
			$text = $question->question_title;
			$message = "Пользователь {name} отредактировал вопрос, на который Вы подписаны, \"{text}\".";
			$link = $answer->getRecordLink(true);
		} elseif ($type_id == Notification::FOLLOWED_USER_QUESTION_EDIT) {
			$text = $question->question_title;
			$message = "Пользователь {name}, на которого Вы подписаны, отредактировал вопрос \"{text}\".";
			$link = $answer->getRecordLink(true);
		}
		return [$text, $discipline, $message, $link];
	}


	protected static function sendMessage(array $params)
	{
		$chat_id = ArrayHelper::getValue($params, 'chat_id');
		$author = ArrayHelper::getValue($params, 'author');
		$name = $author->name;
		list($text, $discipline, $message, $link) = static::getMessageParams($params);

		$message = Yii::t('app', $message, ['name' => $name, 'text' => $text, 'discipline' => $discipline]);
		$bot = new Bot;
		$bot->messageNotification($chat_id, $message, $link);
	}

	public static function addQuestion(Question $question) {
		$discipline_id = $question->discipline_id;
		$author = $question->author;
		$author_id = $author->user_id;
		$followDisciplineIds = FollowDiscipline::getUserIds($discipline_id, $author_id);
		$followUserIds = FollowUser::getUserIds($author_id);
		$userIds = array_merge($followDisciplineIds, $followUserIds);
		$userIds = array_unique($userIds);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['OR',
				['followed_discipline_question_create' => true],
				['followed_user_question_create' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$type_id = null;
			if ($user->user_id != $author_id) {
				if ($user->data->botSettings->followed_discipline_question_create) {
					$type_id = Notification::FOLLOWED_DISCIPLINE_QUESTION_CREATE;

				} elseif ($user->data->botSettings->followed_user_question_create) {
					$type_id = Notification::FOLLOWED_USER_QUESTION_CREATE;

				}
			}
			if (!is_null($type_id)) {
				static::sendMessage([
					'chat_id' => $user->data->chat->chat_id,
					'author' => $author,
					'question' => $question,
					'type_id' => $type_id,
				]);
			}
		}
	}

	public static function addAnswer(Answer $answer) {
		$author = $answer->author;
		$author_id = $author->user_id;
		$question = $answer->question;
		$question_id = $question->id;
		$question_author_id = $answer->question->user_id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['OR',
				['my_question_answer' => true],
				['followed_question_answer' => true],
				['followed_user_answer' => true],
				['followed_discipline_question_answer' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$type_id = null;
			if ($user->user_id == $question_author_id) {
				if ($user->data->botSettings->my_question_answer) {
					$type_id = Notification::MY_QUESTION_ANSWER;
				}
			} else {
				if ($user->data->botSettings->followed_question_answer) {
					$type_id = Notification::FOLLOWED_QUESTION_ANSWER;
				} elseif ($user->data->botSettings->followed_user_answer) {
					$type_id = Notification::FOLLOWED_USER_ANSWER;
				} elseif ($user->data->botSettings->followed_discipline_question_answer) {
					$type_id = Notification::FOLLOWED_DISCIPLINE_QUESTION_ANSWER;
				}
			}
			if (!is_null($type_id)) {
				static::sendMessage([
					'chat_id' => $user->data->chat->chat_id,
					'author' => $author,
					'question' => $question,
					'answer' => $answer,
					'type_id' => $type_id,
				]);
			}
		}
	}

	public static function addQuestionComment(Comment $comment) {
		$author = $comment->author;
		$author_id = $author->user_id;
		$question = $comment->question;
		$question_id = $question->id;
		$question_author_id = $comment->question->user_id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['OR',
				['my_question_comment' => true],
				['followed_question_comment' => true],
				['followed_user_comment' => true],
				['followed_discipline_question_comment' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$type_id = null;
			if ($user->user_id == $question_author_id) {
				if ($user->data->botSettings->my_question_comment) {
					$type_id = Notification::MY_QUESTION_COMMENT;
				}
			} else {
				if ($user->data->botSettings->followed_question_comment) {
					$type_id = Notification::FOLLOWED_QUESTION_COMMENT;
				} elseif ($user->data->botSettings->followed_user_comment) {
					$type_id = Notification::FOLLOWED_USER_COMMENT;
				} elseif ($user->data->botSettings->followed_discipline_question_comment) {
					$type_id = Notification::FOLLOWED_DISCIPLINE_QUESTION_COMMENT;
				}
			}
			if (!is_null($type_id)) {
				static::sendMessage([
					'chat_id' => $user->data->chat->chat_id,
					'author' => $author,
					'question' => $question,
					'comment' => $comment,
					'type_id' => $type_id,
				]);
			}
		}
	}

	public static function addAnswerComment(Comment $comment) {
		$author = $comment->author;
		$author_id = $author->user_id;
		$question_id = $comment->question_id;
		if ($comment->isForAnswer) {
			$answer = $comment->answer;
			$question = null;
		} else {
			$answer = null;
			$question = $comment->question;
		}

		$question_author_id = $comment->question->user_id;
		$answer_user_id = $comment->answer->user_id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['OR',
				['my_question_answer_comment' => true],
				['followed_question_answer_comment' => true],
				['followed_question_my_answer_comment' => true],
				['followed_user_comment' => true],
				['followed_discipline_question_answer_comment' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$type_id = null;
			if ($user->user_id == $question_author_id) {
				if ($user->data->botSettings->my_question_answer_comment) {
					$type_id = Notification::MY_QUESTION_ANSWER_COMMENT;
				}
			} elseif ($user->user_id == $answer_user_id) {
				if ($user->data->botSettings->followed_question_my_answer_comment) {
					$type_id = Notification::FOLLOWED_QUESTION_MY_ANSWER_COMMENT;
				}
			} else {
				if ($user->data->botSettings->followed_question_answer_comment) {
					$type_id = Notification::FOLLOWED_QUESTION_ANSWER_COMMENT;
				} elseif ($user->data->botSettings->followed_user_comment) {
					$type_id = Notification::FOLLOWED_USER_COMMENT;
				} elseif ($user->data->botSettings->followed_discipline_question_answer_comment) {
					$type_id = Notification::FOLLOWED_DISCIPLINE_QUESTION_ANSWER_COMMENT;
				}
			}
			if (!is_null($type_id)) {
				static::sendMessage([
					'chat_id' => $user->data->chat->chat_id,
					'author' => $author,
					'question' => $question,
					'answer' => $answer,
					'comment' => $comment,
					'type_id' => $type_id,
				]);
			}
		}
	}

	public static function addQuestionAnswered(Answer $answer) {
		$author = $answer->author;
		$author_id = $author->user_id;
		$question_id = $answer->question_id;
		$question = $answer->question;
		$answer_id = $answer->id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['OR',
				['followed_question_answered' => true],
				['followed_user_question_answered' => true],
				['followed_discipline_question_answered' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$type_id = null;
			if ($user->data->botSettings->followed_question_answered) {
				$type_id = Notification::FOLLOWED_QUESTION_ANSWERED;
			} elseif ($user->data->botSettings->followed_user_question_answered) {
				$type_id = Notification::FOLLOWED_USER_QUESTION_ANSWERED;
			} elseif ($user->data->botSettings->followed_discipline_question_answered) {
				$type_id = Notification::FOLLOWED_DISCIPLINE_QUESTION_ANSWERED;
			}
			if (!is_null($type_id)) {
				static::sendMessage([
					'chat_id' => $user->data->chat->chat_id,
					'author' => $author,
					'question' => $question,
					'answer' => $answer,
					'type_id' => $type_id,
				]);
			}
		}
	}

	/*
	public function addMention(Comment $comment, array $userIds) {
		$id = $comment->id;
		$author_id = $comment->user_id;
		$question_id = $comment->question_id;
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['mention' => true])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$record = new self;
			$record->user_id => $user->user_id,
			$record->author_id = $author_id;
			$record->question_id = $question_id;
			$record->comment_id = $id;
			$type_id = Notification::FOLLOWED_QUESTION_ANSWERED;
		}
	}
	*/

	/*public static function addInvite(Question $question, int $author_id, int $user_id) {
		$question_id = $question->id;
		$user = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['invite_as_expert' => true])
			->andWhere(['user.user_id' => $user_id])
			->one();
		if ($user) {
			$record = new self;
			$record->user_id = $user_id;
			$record->author_id = $author_id;
			$record->question_id = $question_id;
			$type_id = Notification::INVITE_AS_EXPERT;
		}
	}*/

	public static function addLikeAnswer(Answer $answer, int $author_id) {
		$question = $answer->question;
		$author = $answer->author;
		$user = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['my_answer_like' => true])
			->andWhere(['user.user_id' => $answer->user_id])
			->one();
		if ($user) {
			static::sendMessage([
				'chat_id' => $user->data->chat->chat_id,
				'author' => $author,
				'question' => $question,
				'answer' => $answer,
				'type_id' => Notification::MY_ANSWER_LIKE,
			]);
		}
	}

	public static function addQuestionEdit(Question $question) {
		$question_id = $question->id;
		$author = $question->author;
		$author_id = $author->user_id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['OR',
				['followed_question_edit' => true],
				['followed_user_question_edit' => true],
				['followed_discipline_question_edit' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$type_id = null;
			if ($user->data->botSettings->followed_question_edit) {
				$type_id = Notification::FOLLOWED_QUESTION_EDIT;
			} elseif ($user->data->botSettings->followed_user_question_edit) {
				$type_id = Notification::FOLLOWED_USER_QUESTION_EDIT;
			} elseif ($user->data->botSettings->followed_discipline_question_edit) {
				$type_id = Notification::FOLLOWED_DISCIPLINE_QUESTION_EDIT;
			}
			if (!is_null($type_id)) {
				static::sendMessage([
					'chat_id' => $user->data->chat->chat_id,
					'author' => $author,
					'question' => $question,
					'type_id' => $type_id,
				]);
			}
		}
	}

	public static function addAnswerEdit(Answer $answer) {
		$author = $answer->author;
		$author_id = $author->user_id;
		$question_id = $answer->question_id;
		$question = $answer->question;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.botSettings'])
			->innerJoinWith(['data.chat'])
			->where(['answer_edit' => true])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			static::sendMessage([
				'chat_id' => $user->data->chat->chat_id,
				'author' => $author,
				'question' => $question,
				'answer' => $answer,
				'type_id' => Notification::ANSWER_EDIT,
			]);
		}
	}

}
